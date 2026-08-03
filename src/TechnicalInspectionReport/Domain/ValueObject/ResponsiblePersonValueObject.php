<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Domain\ValueObject;

use App\TechnicalInspectionReport\Exception\InvalidResponsiblePersonException;

class ResponsiblePersonValueObject
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidResponsiblePersonException('O responsável pelo relatório é obrigatório.');
        }

        $this->value = preg_replace('/\s+/u', ' ', $value) ?? $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $person): bool
    {
        return $this->value === $person->value;
    }
}
