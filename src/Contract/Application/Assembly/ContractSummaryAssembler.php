<?php

declare(strict_types=1);

namespace App\Contract\Application\Assembly;

use App\Contract\Application\DTO\ContractExecutionDeadlineOutputDTO;
use App\Contract\Application\DTO\ContractExtractDTO;
use App\Contract\Application\DTO\ContractReadjustmentOutputDTO;
use App\Contract\Application\DTO\MunicipalityContractReferenceDTO;
use App\Contract\Application\DTO\ValueAdditiveOutputDTO;
use App\Contract\Application\Interfaces\Assembly\ContractSummaryAssemblerInterface;
use App\Contract\Application\Trait\NormalizesContractSummaryRecordsTrait;
use App\Contract\Application\Trait\ResolvesContractSummaryMovementsTrait;
use App\Contract\Application\Trait\ResolvesContractSummarySituationTrait;
use App\Contract\Application\Trait\ResolvesContractSummaryStatusTrait;
use App\Contract\Domain\Entity\ContractEntity;

class ContractSummaryAssembler implements ContractSummaryAssemblerInterface
{
    use NormalizesContractSummaryRecordsTrait;
    use ResolvesContractSummaryMovementsTrait;
    use ResolvesContractSummarySituationTrait;
    use ResolvesContractSummaryStatusTrait;

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
}
