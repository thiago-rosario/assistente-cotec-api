<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Domain\ValueObject;

use App\TechnicalInspectionReport\Exception\InvalidExternalMessageIdException;

readonly class ExternalMessageIdValueObject
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidExternalMessageIdException(
                'O identificador externo da mensagem é obrigatório.',
            );
        }

        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $id): bool
    {
        return $this->value === $id->value;
    }
}
