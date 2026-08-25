<?php

declare(strict_types=1);

namespace App\Contract\Infra\Message;

use DateTimeImmutable;
use DateTimeInterface;
use Stringable;

class WhatsappContractRecordValueFormatter
{
    /**
     * @var list<string>
     */
    private const array DateFields = [
        'entryDate',
        'publicationDate',
        'ceirfEntryDate',
        'ceirfLastMovementDate',
        'validityEndDate',
        'executionEndDate',
    ];

    public function contractValue(object $record, string $key, bool $monetary = false): string
    {
        $value = $record->{$key} ?? null;

        return in_array($key, self::DateFields, true)
            ? $this->dateValue($value)
            : $this->value($value, $monetary);
    }

    public function recordValue(object $record, string $key): ?string
    {
        if (! property_exists($record, $key)) {
            return null;
        }

        $value = $this->contractValue($record, $key);

        return $value === 'Não informado' ? null : $value;
    }

    public function value(mixed $value, bool $monetary = false): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('d/m/Y');
        }

        if ($monetary) {
            return $this->monetaryValue($value);
        }

        if ($value instanceof Stringable) {
            $value = (string) $value;
        }

        if (! is_scalar($value)) {
            return 'Não informado';
        }

        $value = trim((string) $value);

        return $value === '' ? 'Não informado' : $value;
    }

    private function monetaryValue(mixed $value): string
    {
        if ($value instanceof Stringable) {
            $value = (string) $value;
        }

        if (is_string($value)) {
            $value = trim($value);

            if ($value === '') {
                return 'Não informado';
            }

            if (str_starts_with($value, 'R$')) {
                return $value;
            }
        }

        if (! is_numeric($value)) {
            return $this->value($value);
        }

        return 'R$ '.number_format((float) $value, 2, ',', '.');
    }

    private function dateValue(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('d/m/Y');
        }

        if ($value instanceof Stringable) {
            $value = (string) $value;
        }

        if (is_string($value)) {
            $value = trim($value);

            if ($value === '') {
                return 'Não informado';
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
                $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
                $errors = DateTimeImmutable::getLastErrors();

                if ($date instanceof DateTimeImmutable
                    && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                    return $date->format('d/m/Y');
                }
            }
        }

        return $this->value($value);
    }
}
