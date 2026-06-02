<?php

declare(strict_types=1);

namespace App\Core\Infra\Trait;

use DateTimeImmutable;
use Throwable;

trait CastsSpreadsheetValues
{
    private function toString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function toInt(mixed $value): ?int
    {
        $value = trim((string) $value);

        return is_numeric($value) ? (int) $value : null;
    }

    private function toFloat(mixed $value): ?float
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = str_replace(['R$', '.', ','], ['', '', '.'], $value);

        return is_numeric($value) ? (float) $value : null;
    }

    private function toDateTimeImmutable(mixed $value): ?DateTimeImmutable
    {
        $value = $this->toString($value);

        if ($value === null) {
            return null;
        }

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d'] as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value);

            if ($date instanceof DateTimeImmutable) {
                return $date;
            }
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Throwable) {
            return null;
        }
    }
}
