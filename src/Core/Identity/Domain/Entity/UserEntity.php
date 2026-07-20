<?php

declare(strict_types=1);

namespace App\Core\Identity\Domain\Entity;

use App\Core\Identity\Domain\Trait\DateTimeConversionTrait;
use App\Core\Identity\Domain\Trait\MethodsMagicsTrait;
use App\Core\Identity\Domain\Validation\UserDomainValidation;
use App\Core\Identity\Domain\ValueObject\Login;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * @property-read string $id
 * @property-read string $name
 * @property-read Login $login
 * @property-read DateTimeImmutable $createdAt
 * @property-read DateTimeImmutable $updatedAt
 */
final class UserEntity
{
    use DateTimeConversionTrait;
    use MethodsMagicsTrait;

    protected readonly string $id;

    protected readonly string $name;

    protected readonly Login $login;

    protected readonly DateTimeImmutable $createdAt;

    protected readonly DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        string $name,
        Login|string $login,
        DateTimeInterface|string|null $createdAt = null,
        DateTimeInterface|string|null $updatedAt = null,
    ) {
        $this->id = trim($id);
        $this->name = trim($name);
        $this->login = $login instanceof Login ? $login : Login::fromString($login);
        $this->createdAt = self::dateTime($createdAt);
        $this->updatedAt = self::dateTime($updatedAt ?? $this->createdAt);

        UserDomainValidation::validateId($this->id);
        UserDomainValidation::validateName($this->name);
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

    public function isSameUser(string $userId): bool
    {
        return $this->id === trim($userId);
    }

    /**
     * @return array{id: string, name: string, login: string}
     */
    public function toAuthorizationArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'login' => (string) $this->login,
        ];
    }
}
