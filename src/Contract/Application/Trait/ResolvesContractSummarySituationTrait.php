<?php

declare(strict_types=1);

namespace App\Contract\Application\Trait;

use App\Contract\Application\DTO\ContractExecutionDeadlineOutputDTO;
use App\Contract\Application\DTO\ContractReadjustmentOutputDTO;
use App\Contract\Application\DTO\ValueAdditiveOutputDTO;
use DateTimeImmutable;

/**
 * Resolves the current situation and the most relevant active pending item.
 *
 * Status normalization is supplied by ResolvesContractSummaryStatusTrait;
 * dates and empty-value handling come from the other summary traits.
 */
trait ResolvesContractSummarySituationTrait
{
    private function currentSituation(
        ?string $contractSituation,
        array $valueAdditives,
        array $readjustments,
        array $executionDeadlines,
        ?string $pending,
    ): ?string {
        if ($pending !== null) {
            $contractSituation = $this->nullableValue($contractSituation);

            return $contractSituation !== null && $this->isActiveStatus($contractSituation)
                ? $this->sentenceCase($contractSituation)
                : 'Em acompanhamento';
        }

        $status = $this->statusLabel($contractSituation);

        if ($status !== null) {
            return $this->sentenceCase($status);
        }

        return $this->sentenceCase($this->latestModuleSituation(
            $valueAdditives,
            $readjustments,
            $executionDeadlines,
        ));
    }

    private function currentPending(array $valueAdditives, array $readjustments, array $executionDeadlines): ?string
    {
        $candidates = [];

        foreach ($valueAdditives as $record) {
            $this->addPendingCandidate(
                $candidates,
                'Aditivo',
                $record->situation ?? $record->status,
                $record->currentLocation,
                $this->latestDate([
                    $this->parseDate($record->entryDate),
                    $this->parseDate($record->publicationDate),
                ]),
            );
        }

        foreach ($readjustments as $record) {
            $this->addPendingCandidate(
                $candidates,
                'Reajuste',
                $this->readjustmentPendingStatus($record),
                $record->location,
                $this->latestDate([
                    $record->entryDate,
                    $record->ceirfEntryDate,
                    $record->ceirfLastMovementDate,
                    $record->publicationDate,
                ]),
            );
        }

        foreach ($executionDeadlines as $record) {
            $this->addPendingCandidate(
                $candidates,
                'Aditivo de prazo',
                $record->deadlineAddendumStatus,
                $record->location,
                $this->latestDate([$record->entryDate, $record->publicationDate]),
            );
        }

        usort($candidates, static function (array $first, array $second): int {
            if ($first['date'] === null && $second['date'] === null) {
                return 0;
            }

            if ($first['date'] === null) {
                return -1;
            }

            if ($second['date'] === null) {
                return 1;
            }

            return $first['date'] <=> $second['date'];
        });

        $candidate = end($candidates);

        if ($candidate === false) {
            return null;
        }

        return $candidate['description'];
    }

    private function readjustmentPendingStatus(ContractReadjustmentOutputDTO $record): ?string
    {
        if ($this->isActiveStatus($record->status)) {
            return $record->status;
        }

        return $this->isActiveStatus($record->paymentSituation)
            ? $record->paymentSituation
            : null;
    }

    /**
     * @param  list<ValueAdditiveOutputDTO>  $valueAdditives
     * @param  list<ContractReadjustmentOutputDTO>  $readjustments
     * @param  list<ContractExecutionDeadlineOutputDTO>  $executionDeadlines
     */
    private function latestModuleSituation(
        array $valueAdditives,
        array $readjustments,
        array $executionDeadlines,
    ): ?string {
        /** @var list<array{date: ?DateTimeImmutable, status: string}> $candidates */
        $candidates = [];

        foreach ($valueAdditives as $record) {
            $status = $record->situation ?? $record->status;

            if ($this->nullableValue($status) !== null) {
                $candidates[] = [
                    'date' => $this->latestDate([
                        $this->parseDate($record->entryDate),
                        $this->parseDate($record->publicationDate),
                    ]),
                    'status' => $status,
                ];
            }
        }

        foreach ($readjustments as $record) {
            $status = $this->statusLabel($record->status);

            if ($status !== null) {
                $candidates[] = [
                    'date' => $this->latestDate([
                        $record->entryDate,
                        $record->ceirfEntryDate,
                        $record->ceirfLastMovementDate,
                        $record->publicationDate,
                    ]),
                    'status' => $status,
                ];
            }
        }

        foreach ($executionDeadlines as $record) {
            $status = $this->deadlineStatus($record);

            if ($status !== null) {
                $candidates[] = [
                    'date' => $this->latestDate([$record->entryDate, $record->publicationDate]),
                    'status' => $status,
                ];
            }
        }

        $latestDate = null;
        $latestStatus = null;
        $fallbackStatus = null;

        foreach ($candidates as $candidate) {
            $fallbackStatus ??= $candidate['status'];

            if ($candidate['date'] !== null
                && ($latestDate === null || $candidate['date'] > $latestDate)) {
                $latestDate = $candidate['date'];
                $latestStatus = $candidate['status'];
            }
        }

        return $this->statusLabel($latestStatus ?? $fallbackStatus);
    }

    /**
     * @param  list<array{date: ?DateTimeImmutable, description: string}>  $candidates
     */
    private function addPendingCandidate(
        array &$candidates,
        string $module,
        ?string $status,
        ?string $location,
        ?DateTimeImmutable $date,
    ): void {
        if (! $this->isActiveStatus($status)) {
            return;
        }

        $description = $module.' '.$this->statusLabel($status);

        if ($this->nullableValue($location) !== null) {
            $description .= ' na '.trim($location);
        }

        $candidates[] = ['date' => $date, 'description' => $description];
    }
}
