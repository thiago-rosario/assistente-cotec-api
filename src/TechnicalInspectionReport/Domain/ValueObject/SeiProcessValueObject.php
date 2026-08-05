<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Domain\ValueObject;

use App\TechnicalInspectionReport\Exception\InvalidSeiProcessException;

readonly class SeiProcessValueObject
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '' || preg_match('/^\d{3}\.\d{4}\.\d{4}\.\d{7}-\d{2}$/', $value) !== 1) {
            throw new InvalidSeiProcessException('O processo SEI informado possui formato inválido.');
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function normalized(): string
    {
        return $this->value;
    }

    public function equals(self $process): bool
    {
        return $this->value === $process->value;
    }
}
