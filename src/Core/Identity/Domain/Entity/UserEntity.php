<?php

declare(strict_types=1);

namespace App\Core\Identity\Domain\Entity;

use App\Core\Identity\Domain\Trait\DateTimeConversionTrait;
use App\Core\Identity\Domain\Trait\MethodsMagicsTrait;
use App\Core\Identity\Domain\Validation\UserDomainValidation;
use App\Core\Identity\Domain\ValueObject\Login;
use App\Models\User;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * @property-read string|null $id
 * @property-read string $name
 * @property-read Login $login
 * @property-read DateTimeImmutable $createdAt
 * @property-read DateTimeImmutable $updatedAt
 */
final class UserEntity
{
    use DateTimeConversionTrait;
    use MethodsMagicsTrait;

    protected readonly ?string $id;

    protected readonly string $name;

    protected readonly Login $login;

    protected readonly DateTimeImmutable $createdAt;

    protected readonly DateTimeImmutable $updatedAt;

    public function __construct(
        ?string $id,
        string $name,
        Login|string $login,
        DateTimeInterface|string|null $createdAt = null,
        DateTimeInterface|string|null $updatedAt = null,
    ) {
        $this->id = $id === null ? null : trim($id);
        $this->name = trim($name);
        $this->login = $login instanceof Login ? $login : Login::fromString($login);
        $this->createdAt = self::dateTime($createdAt);
        $this->updatedAt = self::dateTime($updatedAt ?? $this->createdAt);

        if ($this->id !== null) {
            UserDomainValidation::validateId($this->id);
        }

        UserDomainValidation::validateName($this->name);
    }

    public static function newRegistration(
        string $name,
        Login|string $login,
        DateTimeInterface|string|null $registeredAt = null,
    ): self {
        $registeredAt = self::dateTime($registeredAt);

        return new self(
            id: null,
            name: $name,
            login: $login,
            createdAt: $registeredAt,
            updatedAt: $registeredAt,
        );
    }

    public static function identified(
        string $id,
        string $name,
        Login|string $login,
        DateTimeInterface|string|null $createdAt = null,
        DateTimeInterface|string|null $updatedAt = null,
    ): self {
        return new self(
            id: $id,
            name: $name,
            login: $login,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public static function fromModel(User $user): self
    {
        return self::identified(
            id: (string) $user->getKey(),
            name: $user->name,
            login: $user->email,
            createdAt: $user->created_at,
            updatedAt: $user->updated_at,
        );
    }

    public function isSameUser(string $userId): bool
    {
        return $this->id === trim($userId);
    }

    /**
     * @return array{id: string, name: string, login: string}
     */
    public function toAuthorizationArray(): array
    {
        UserDomainValidation::validateId((string) $this->id);

        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'login' => (string) $this->login,
        ];
    }

    /**
     * @return array{
     *     name: string,
     *     email: string,
     *     created_at: DateTimeImmutable,
     *     updated_at: DateTimeImmutable
     * }
     */
    public function toPersistenceArray(): array
    {
        return [
            'name' => $this->name,
            'email' => (string) $this->login,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
