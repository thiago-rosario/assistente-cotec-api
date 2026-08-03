<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Domain\ValueObject;

use App\TechnicalInspectionReport\Exception\InvalidMunicipalityException;

class MunicipalityValueObject
{
    private string $value;

    private string $normalizedValue;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new InvalidMunicipalityException('O município da vistoria técnica é obrigatório.');
        }

        $this->value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        $this->normalizedValue = self::normalize($this->value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function normalized(): string
    {
        return $this->normalizedValue;
    }

    public function equals(self $municipality): bool
    {
        return $this->normalizedValue === $municipality->normalizedValue;
    }

    private static function normalize(string $value): string
    {
        $normalized = mb_strtolower($value);
        $ascii = strtr($normalized, [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
            'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ñ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
        ]);

        return preg_replace('/\s+/u', ' ', trim($ascii)) ?? $ascii;
    }
}
