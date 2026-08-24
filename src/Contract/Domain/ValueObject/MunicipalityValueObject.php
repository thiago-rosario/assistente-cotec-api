<?php

declare(strict_types=1);

namespace App\Contract\Domain\ValueObject;

use InvalidArgumentException;

readonly class MunicipalityValueObject
{
    public readonly string $value;

    public readonly string $normalized;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException('The municipality cannot be empty.');
        }

        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        $this->value = $value;
        $this->normalized = mb_strtoupper($value, 'UTF-8');
    }

    public function equals(self $municipality): bool
    {
        return $this->normalized === $municipality->normalized;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
