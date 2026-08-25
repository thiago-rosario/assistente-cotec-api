<?php

declare(strict_types=1);

namespace App\Contract\Domain\ValueObject;

use App\Contract\Exception\SeiProcessCannotBeEmptyException;

readonly class SeiProcessValueObject
{
    public readonly string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new SeiProcessCannotBeEmptyException;
        }

        $this->value = $value;
    }

    public function equals(self $seiProcess): bool
    {
        return $this->value === $seiProcess->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
