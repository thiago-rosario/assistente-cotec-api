<?php

declare(strict_types=1);

namespace App\Core\Identity\Domain\ValueObject;

use App\Core\Identity\Domain\Trait\MethodsMagicsTrait;
use App\Core\Identity\Domain\Validation\UserDomainValidation;

/**
 * @property-read string $value
 */
final class Login
{
    use MethodsMagicsTrait;

    protected readonly string $value;

    public function __construct(string $value)
    {
        $this->value = trim($value);

        UserDomainValidation::validateLogin($this->value);
    }

    public static function fromString(string $login): self
    {
        return new self($login);
    }

    public function equals(self $login): bool
    {
        return $this->value === $login->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
