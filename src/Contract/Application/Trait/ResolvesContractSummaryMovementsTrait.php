<?php

declare(strict_types=1);

namespace App\Contract\Application\Trait;

use App\Contract\Application\DTO\ContractExecutionDeadlineOutputDTO;
use App\Contract\Application\DTO\ContractReadjustmentOutputDTO;
use App\Contract\Application\DTO\ValueAdditiveOutputDTO;
use DateTimeImmutable;

/**
 * Resolves movement dates used by the general contract extract.
 */
trait ResolvesContractSummaryMovementsTrait
{
    /**
     * @param  list<ValueAdditiveOutputDTO>  $records
     */
    private function latestPublishedValue(array $records): ?float
    {
        $latestDate = null;
        $latestValue = null;

        foreach ($records as $record) {
            if ($record->publishedValue === null) {
                continue;
            }

            $movement = $this->latestDate([
                $this->parseDate($record->entryDate),
                $this->parseDate($record->publicationDate),
            ]);

            if ($latestDate === null || ($movement !== null && $movement >= $latestDate)) {
                $latestDate = $movement;
                $latestValue = $record->publishedValue;
            }
        }

        return $latestValue;
    }

    private function parseDate(?string $value): ?DateTimeImmutable
    {
        $value = $this->nullableValue($value);

        if ($value === null) {
            return null;
        }

        foreach (['!Y-m-d', '!d/m/Y'] as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value);
            $errors = DateTimeImmutable::getLastErrors();

            if ($date instanceof DateTimeImmutable
                && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $date;
            }
        }

        return null;
    }

    /**
     * @param  list<?DateTimeImmutable>  $dates
     */
    private function latestDate(array $dates): ?DateTimeImmutable
    {
        $latest = null;

        foreach ($dates as $date) {
            if ($date !== null && ($latest === null || $date > $latest)) {
                $latest = $date;
            }
        }

        return $latest;
    }

    /**
     * @param  list<ValueAdditiveOutputDTO>  $valueAdditives
     * @param  list<ContractReadjustmentOutputDTO>  $readjustments
     * @param  list<ContractExecutionDeadlineOutputDTO>  $executionDeadlines
     */
    private function lastMovementDate(
        array $valueAdditives,
        array $readjustments,
        array $executionDeadlines,
    ): ?DateTimeImmutable {
        $dates = [];

        foreach ($valueAdditives as $record) {
            $dates[] = $this->latestDate([
                $this->parseDate($record->entryDate),
                $this->parseDate($record->publicationDate),
            ]);
        }

        foreach ($readjustments as $record) {
            $dates[] = $this->latestDate([
                $record->entryDate,
                $record->ceirfEntryDate,
                $record->ceirfLastMovementDate,
                $record->publicationDate,
            ]);
        }

        foreach ($executionDeadlines as $record) {
            $dates[] = $this->latestDate([$record->entryDate, $record->publicationDate]);
        }

        return $this->latestDate($dates);
    }
}
