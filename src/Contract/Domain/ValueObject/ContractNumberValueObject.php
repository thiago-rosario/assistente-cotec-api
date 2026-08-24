<?php

declare(strict_types=1);

namespace App\Contract\Domain\ValueObject;

use InvalidArgumentException;

readonly class ContractNumberValueObject
{
    public readonly string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException('The contract number cannot be empty.');
        }

        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s*\/\s*/u', '/', $value) ?? $value;

        $this->value = $value;
    }

    public function equals(self $contractNumber): bool
    {
        return $this->value === $contractNumber->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
