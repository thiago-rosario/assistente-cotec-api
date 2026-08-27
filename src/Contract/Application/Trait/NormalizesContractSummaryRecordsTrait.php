<?php

declare(strict_types=1);

namespace App\Contract\Application\Trait;

use App\Contract\Application\DTO\ContractExecutionDeadlineOutputDTO;
use App\Contract\Application\DTO\ContractReadjustmentOutputDTO;
use App\Contract\Application\DTO\ValueAdditiveOutputDTO;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;

/**
 * Normalizes, deduplicates and selects movement data for contract extracts.
 */
trait NormalizesContractSummaryRecordsTrait
{
    /**
     * @param  list<ValueAdditiveOutputDTO>  $records
     * @return list<ValueAdditiveOutputDTO>
     */
    private function uniqueValueAdditives(array $records): array
    {
        $unique = [];

        foreach ($records as $record) {
            $movement = $this->latestDate([
                $this->parseDate($record->entryDate),
                $this->parseDate($record->publicationDate),
            ]);
            $key = implode('|', [
                $this->contractKey($record->contractNumber),
                $this->textKey($record->seiProcess),
                $this->textKey($record->type),
                $movement?->format('Y-m-d') ?? $this->textKey($record->additiveNumber),
                $this->textKey($record->additiveNumber),
            ]);
            $unique[$key] ??= $record;
        }

        return array_values($unique);
    }

    /**
     * @param  list<ContractReadjustmentOutputDTO>  $records
     * @return list<ContractReadjustmentOutputDTO>
     */
    private function uniqueReadjustments(array $records): array
    {
        $unique = [];

        foreach ($records as $record) {
            $movement = $this->latestDate([
                $record->entryDate,
                $record->ceirfEntryDate,
                $record->ceirfLastMovementDate,
                $record->publicationDate,
            ]);
            $key = implode('|', [
                $this->contractKey($record->contractNumber),
                $this->textKey($record->seiProcess),
                $movement?->format('Y-m-d') ?? $this->textKey($record->apostilleNumber),
                $this->textKey($record->apostilleNumber),
            ]);
            $unique[$key] ??= $record;
        }

        return array_values($unique);
    }

    /**
     * @param  list<ContractExecutionDeadlineOutputDTO>  $records
     * @return list<ContractExecutionDeadlineOutputDTO>
     */
    private function uniqueExecutionDeadlines(array $records): array
    {
        $unique = [];

        foreach ($records as $record) {
            $movement = $this->latestDate([$record->entryDate, $record->publicationDate]);
            $key = implode('|', [
                $this->contractKey($record->contractNumber),
                $this->textKey($record->seiProcess),
                $movement?->format('Y-m-d')
                    ?? $this->textKey($record->executionEndDate?->format('Y-m-d')),
                $this->textKey($record->observation),
            ]);
            $unique[$key] ??= $record;
        }

        return array_values($unique);
    }

    /**
     * @param  list<string|null>  $values
     */
    private function firstValue(array $values): ?string
    {
        foreach ($values as $value) {
            $value = $this->nullableValue($value);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    private function nullableValue(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return $value === null || $value === '' || in_array($value, ['-', '/'], true) ? null : $value;
    }

    private function textKey(?string $value): string
    {
        $value = $this->nullableValue($value);

        return $value === null ? '' : mb_strtoupper($value, 'UTF-8');
    }

    private function contractKey(string $contractNumber): string
    {
        return (new ContractNumberValueObject($contractNumber))->equivalenceKey();
    }
}
