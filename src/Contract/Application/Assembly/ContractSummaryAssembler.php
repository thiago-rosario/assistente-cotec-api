<?php

declare(strict_types=1);

namespace App\Contract\Application\Assembly;

use App\Contract\Application\DTO\ContractExecutionDeadlineOutputDTO;
use App\Contract\Application\DTO\ContractExtractDTO;
use App\Contract\Application\DTO\ContractReadjustmentOutputDTO;
use App\Contract\Application\DTO\MunicipalityContractReferenceDTO;
use App\Contract\Application\DTO\ValueAdditiveOutputDTO;
use App\Contract\Application\Interfaces\Assembly\ContractSummaryAssemblerInterface;
use App\Contract\Domain\Entity\ContractEntity;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use DateTimeImmutable;

class ContractSummaryAssembler implements ContractSummaryAssemblerInterface
{
    /**
     * @param  list<ValueAdditiveOutputDTO>  $valueAdditives
     * @param  list<ContractReadjustmentOutputDTO>  $readjustments
     * @param  list<ContractExecutionDeadlineOutputDTO>  $executionDeadlines
     */
    public function assemble(
        ContractEntity $contract,
        MunicipalityContractReferenceDTO $reference,
        ?string $municipality,
        array $valueAdditives,
        array $readjustments,
        array $executionDeadlines,
    ): ContractExtractDTO {
        $valueAdditives = $this->uniqueValueAdditives($valueAdditives);
        $readjustments = $this->uniqueReadjustments($readjustments);
        $executionDeadlines = $this->uniqueExecutionDeadlines($executionDeadlines);
        $pending = $this->currentPending($valueAdditives, $readjustments, $executionDeadlines);
        $contractSituation = $this->currentSituation(
            $contract->currentSituation,
            $valueAdditives,
            $readjustments,
            $executionDeadlines,
            $pending,
        );

        return new ContractExtractDTO(
            contractNumber: $contract->contractNumber,
            company: $reference->company ?? $contract->company,
            municipality: $this->firstValue([
                $municipality,
                $contract->municipalities[0] ?? null,
                ...array_map(
                    static fn (ValueAdditiveOutputDTO $record): ?string => $record->municipality,
                    $valueAdditives,
                ),
                ...array_map(
                    static fn (ContractExecutionDeadlineOutputDTO $record): ?string => $record->municipality,
                    $executionDeadlines,
                ),
            ]),
            seiProcess: $this->firstValue([
                $contract->seiProcess,
                ...array_map(
                    static fn (ValueAdditiveOutputDTO $record): ?string => $record->seiProcess,
                    $valueAdditives,
                ),
                ...array_map(
                    static fn (ContractReadjustmentOutputDTO $record): ?string => $record->seiProcess,
                    $readjustments,
                ),
                ...array_map(
                    static fn (ContractExecutionDeadlineOutputDTO $record): ?string => $record->seiProcess,
                    $executionDeadlines,
                ),
            ]),
            currentSituation: $contractSituation,
            updatedValue: $contract->updatedValue ?? $this->latestPublishedValue($valueAdditives),
            additivesCount: count($valueAdditives),
            additivesStatus: $this->statusSummary(
                array_map(
                    fn (ValueAdditiveOutputDTO $record): ?string => $record->situation ?? $record->status,
                    $valueAdditives,
                ),
            ),
            readjustmentsCount: count($readjustments),
            readjustmentsStatus: $this->statusSummary(
                array_map(fn (ContractReadjustmentOutputDTO $record): ?string => $this->readjustmentStatus($record), $readjustments),
            ),
            executionDeadlinesStatus: $this->statusSummary(
                array_map(
                    fn (ContractExecutionDeadlineOutputDTO $record): ?string => $this->deadlineStatus($record),
                    $executionDeadlines,
                ),
            ) ?? 'Sem registros',
            lastMovementDate: $this->lastMovementDate($valueAdditives, $readjustments, $executionDeadlines),
            currentPending: $pending,
        );
    }

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
        $contractNumber = (new ContractNumberValueObject($contractNumber))->value;
        $parts = explode('/', $contractNumber, 2);
        $parts[0] = ltrim($parts[0], '0') ?: '0';

        return implode('/', $parts);
    }

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

    private function readjustmentStatus(ContractReadjustmentOutputDTO $record): ?string
    {
        $status = $this->statusLabel($record->status);
        $payment = $this->statusLabel($record->paymentSituation);

        if ($status === null) {
            return $payment;
        }

        if ($status === 'publicado' && in_array($payment, ['pago', 'liquidado'], true)) {
            return 'publicado e liquidado';
        }

        return $status;
    }

    private function deadlineStatus(ContractExecutionDeadlineOutputDTO $record): ?string
    {
        return $this->statusLabel(
            $this->isActiveStatus($record->deadlineAddendumStatus)
                ? $record->deadlineAddendumStatus
                : $record->contractSituation,
        );
    }

    /**
     * @param  list<string|null>  $statuses
     */
    private function statusSummary(array $statuses): ?string
    {
        $counts = [];

        foreach ($statuses as $status) {
            $status = $this->statusLabel($status);

            if ($status !== null) {
                $counts[$status] = ($counts[$status] ?? 0) + 1;
            }
        }

        if ($counts === []) {
            return null;
        }

        uksort($counts, function (string $first, string $second): int {
            $priority = [
                'publicado' => 10,
                'publicado e liquidado' => 20,
                'em execução' => 30,
                'em tramitação' => 80,
                'em análise' => 90,
                'pendente' => 100,
            ];

            return ($priority[$first] ?? 50) <=> ($priority[$second] ?? 50);
        });

        if (count($counts) === 1) {
            $status = array_key_first($counts);
            $count = $counts[$status];

            return sprintf(
                '%d %s %s',
                $count,
                $count === 1 ? 'registro' : 'registros',
                $this->pluralizeStatus($status, $count),
            );
        }

        return implode('; ', array_map(
            fn (string $status, int $count): string => sprintf(
                '%d %s',
                $count,
                $this->pluralizeStatus($status, $count),
            ),
            array_keys($counts),
            array_values($counts),
        ));
    }

    private function pluralizeStatus(string $status, int $count): string
    {
        if ($count === 1) {
            return $status;
        }

        return match ($status) {
            'publicado' => 'publicados',
            'publicado e liquidado' => 'publicados e liquidados',
            'pendente' => 'pendentes',
            default => $status,
        };
    }

    private function statusLabel(?string $status): ?string
    {
        $status = $this->nullableValue($status);

        if ($status === null) {
            return null;
        }

        $normalized = $this->withoutAccents(mb_strtoupper($status, 'UTF-8'));

        return match ($normalized) {
            'PUBLICADO' => 'publicado',
            'EM TRAMITACAO' => 'em tramitação',
            'EM ANALISE' => 'em análise',
            'PENDENTE' => 'pendente',
            'EM EXECUCAO' => 'em execução',
            'PAGO' => 'pago',
            'LIQUIDADO' => 'liquidado',
            default => mb_strtolower($status, 'UTF-8'),
        };
    }

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

        return $this->sentenceCase($this->statusLabel($this->firstValue([
            ...array_map(
                static fn (ValueAdditiveOutputDTO $record): ?string => $record->situation ?? $record->status,
                $valueAdditives,
            ),
            ...array_map(
                static fn (ContractReadjustmentOutputDTO $record): ?string => $record->status,
                $readjustments,
            ),
            ...array_map(
                static fn (ContractExecutionDeadlineOutputDTO $record): ?string => $record->contractSituation,
                $executionDeadlines,
            ),
        ])));
    }

    private function sentenceCase(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return mb_strtoupper(mb_substr($value, 0, 1, 'UTF-8'), 'UTF-8')
            .mb_substr($value, 1, null, 'UTF-8');
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
                $record->status,
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

    private function isActiveStatus(?string $status): bool
    {
        $normalized = $this->withoutAccents(mb_strtoupper($this->nullableValue($status) ?? '', 'UTF-8'));

        foreach (['TRAMIT', 'PEND', 'ANALIS', 'AGUARD', 'APROVAC', 'INSTRU', 'ELABOR', 'ANDAMENTO', 'NAO PUBLIC'] as $term) {
            if (str_contains($normalized, $term)) {
                return true;
            }
        }

        return false;
    }

    private function withoutAccents(string $value): string
    {
        return strtr($value, [
            'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A',
            'É' => 'E', 'Ê' => 'E',
            'Í' => 'I',
            'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',
            'Ú' => 'U',
            'Ç' => 'C',
        ]);
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
    private function lastMovementDate(array $valueAdditives, array $readjustments, array $executionDeadlines): ?DateTimeImmutable
    {
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
