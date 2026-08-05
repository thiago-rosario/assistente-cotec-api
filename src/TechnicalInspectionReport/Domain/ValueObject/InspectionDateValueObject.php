<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Domain\ValueObject;

use App\TechnicalInspectionReport\Exception\InvalidInspectionDateException;
use DateTimeImmutable;

readonly class InspectionDateValueObject
{
    public function __construct(private DateTimeImmutable $value) {}

    public static function fromBrazilianFormat(string $value): self
    {
        $date = DateTimeImmutable::createFromFormat('!d/m/Y', trim($value));
        $errors = DateTimeImmutable::getLastErrors();

        if (
            $date === false
            || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
        ) {
            throw new InvalidInspectionDateException(
                'A data da vistoria técnica deve ser uma data válida no formato dd/mm/aaaa.',
            );
        }

        return new self($date);
    }

    public function value(): DateTimeImmutable
    {
        return $this->value;
    }

    public function formatted(): string
    {
        return $this->value->format('d/m/Y');
    }

    public function iso8601(): string
    {
        return $this->value->format('Y-m-d');
    }

    public function equals(self $date): bool
    {
        return $this->value->format('Y-m-d') === $date->value->format('Y-m-d');
    }
}
