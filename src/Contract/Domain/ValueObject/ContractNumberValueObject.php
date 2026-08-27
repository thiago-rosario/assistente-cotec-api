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
        return $this->equivalenceKey() === $contractNumber->equivalenceKey();
    }

    public function equivalenceKey(): string
    {
        $parts = explode('/', $this->value, 2);

        if (count($parts) !== 2 || ! ctype_digit($parts[0])) {
            return $this->value;
        }

        $parts[0] = ltrim($parts[0], '0') ?: '0';

        return implode('/', $parts);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
