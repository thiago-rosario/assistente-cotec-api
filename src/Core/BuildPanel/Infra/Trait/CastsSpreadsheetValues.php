<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Infra\Trait;

use DateTimeImmutable;
use Illuminate\Support\Str;
use Throwable;

trait CastsSpreadsheetValues
{
    private function rowValue(array $row, string ...$keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                return $row[$key];
            }
        }

        $normalizedRow = [];

        foreach ($row as $key => $value) {
            $normalizedRow[$this->normalizeRowKey((string) $key)] = $value;
        }

        foreach ($keys as $key) {
            $normalizedKey = $this->normalizeRowKey($key);

            if (array_key_exists($normalizedKey, $normalizedRow)) {
                return $normalizedRow[$normalizedKey];
            }
        }

        return null;
    }

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

        foreach (['!j/n/Y', '!j-n-Y', '!Y-m-d', '!n/j/Y'] as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value);

            if ($date instanceof DateTimeImmutable && $this->dateWasParsedWithoutErrors()) {
                return $date;
            }
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function dateWasParsedWithoutErrors(): bool
    {
        $errors = DateTimeImmutable::getLastErrors();

        return $errors === false
            || ((int) $errors['warning_count'] === 0 && (int) $errors['error_count'] === 0);
    }

    private function normalizeRowKey(string $key): string
    {
        return Str::of($key)
            ->trim()
            ->lower()
            ->ascii()
            ->toString();
    }
}
