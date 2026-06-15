<?php

declare(strict_types=1);

namespace App\Core\Infra\Message;

use DateTimeInterface;

class WhatsappRecordValueFormatter
{
    /**
     * @param  array<string, mixed>  $record
     */
    public function technicalNotebookValue(array $record, string $key): string
    {
        if ($key === 'estimatedValue') {
            return $this->estimatedValue($record);
        }

        $value = $record[$key] ?? null;

        if ($value instanceof DateTimeInterface) {
            return $value->format('d/m/Y');
        }

        $value = trim((string) $value);

        return $value === '' ? 'Não informado' : $value;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    public function recordValue(array $record, string $key): ?string
    {
        if (! isset($record[$key])) {
            return null;
        }

        $value = trim((string) $record[$key]);

        return $value === '' ? null : $value;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function estimatedValue(array $record): string
    {
        $value = $record['estimatedValue'] ?? null;

        if (! is_numeric($value)) {
            return 'Não informado';
        }

        return 'R$ '.number_format((float) $value, 2, ',', '.');
    }
}
