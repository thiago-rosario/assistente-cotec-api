<?php

declare(strict_types=1);

namespace App\Contract\Application\Assembly;

use App\Contract\Application\DTO\ContractExecutionDeadlineOutputDTO;
use App\Contract\Application\DTO\ContractReadjustmentOutputDTO;
use App\Contract\Application\DTO\ContractSummaryOutputDTO;
use App\Contract\Application\DTO\MunicipalityContractReferenceDTO;
use App\Contract\Application\DTO\ValueAdditiveOutputDTO;
use App\Contract\Application\Interfaces\Assembly\ContractSummaryAssemblerInterface;
use App\Contract\Domain\Entity\ContractEntity;

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
    ): ContractSummaryOutputDTO {
        $processes = array_values(array_unique(array_filter([
            $contract->seiProcess,
            ...array_map(static fn (ValueAdditiveOutputDTO $record): ?string => $record->seiProcess, $valueAdditives),
            ...array_map(static fn (ContractReadjustmentOutputDTO $record): ?string => $record->seiProcess, $readjustments),
            ...array_map(static fn (ContractReadjustmentOutputDTO $record): ?string => $record->paymentSei, $readjustments),
            ...array_map(static fn (ContractExecutionDeadlineOutputDTO $record): ?string => $record->seiProcess, $executionDeadlines),
        ], static fn (?string $value): bool => $value !== null
            && trim($value) !== ''
            && ! in_array(trim($value), ['-', '/'], true))));

        $statuses = array_values(array_unique(array_filter([
            $contract->currentSituation,
            ...array_map(static fn (ValueAdditiveOutputDTO $record): ?string => $record->status, $valueAdditives),
            ...array_map(static fn (ContractReadjustmentOutputDTO $record): ?string => $record->status, $readjustments),
            ...array_map(static fn (ContractExecutionDeadlineOutputDTO $record): ?string => $record->contractSituation, $executionDeadlines),
            ...array_map(static fn (ContractExecutionDeadlineOutputDTO $record): ?string => $record->deadlineAddendumStatus, $executionDeadlines),
        ], static fn (?string $value): bool => $value !== null && trim($value) !== '')));

        $observations = array_values(array_unique(array_filter([
            ...array_map(static fn (ValueAdditiveOutputDTO $record): ?string => $record->observation, $valueAdditives),
            ...array_map(static fn (ContractReadjustmentOutputDTO $record): ?string => $record->observation, $readjustments),
            ...array_map(static fn (ContractExecutionDeadlineOutputDTO $record): ?string => $record->observation, $executionDeadlines),
        ], static fn (?string $value): bool => $value !== null && trim($value) !== '')));

        return new ContractSummaryOutputDTO(
            contractNumber: $contract->contractNumber,
            company: $reference->company ?? $contract->company,
            seiProcess: $contract->seiProcess,
            municipalities: $contract->municipalities !== []
                ? $contract->municipalities
                : ($municipality === null ? [] : [$municipality]),
            municipality: $municipality ?? ($contract->municipalities[0] ?? null),
            object: $contract->object,
            initialValue: $contract->initialValue,
            updatedValue: $contract->updatedValue,
            validityStartDate: $contract->validityStartDate,
            validityEndDate: $contract->validityEndDate,
            executionDeadline: $contract->executionDeadline,
            currentSituation: $contract->currentSituation,
            valueAdditives: $valueAdditives,
            readjustments: $readjustments,
            executionDeadlines: $executionDeadlines,
            processes: $processes,
            statuses: $statuses,
            observations: $observations,
            additivesCount: count($valueAdditives),
        );
    }
}
