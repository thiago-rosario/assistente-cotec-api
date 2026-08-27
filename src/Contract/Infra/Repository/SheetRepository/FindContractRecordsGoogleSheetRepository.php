<?php

declare(strict_types=1);

namespace App\Contract\Infra\Repository\SheetRepository;

use App\Contract\Application\Interfaces\Adapter\ContractSheetAdapterInterface;
use App\Contract\Application\Interfaces\Mapper\ContractSheetMapperInterface;
use App\Contract\Domain\Entity\ContractEntity;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;

final readonly class FindContractRecordsGoogleSheetRepository
{
    public function __construct(
        private ContractSheetAdapterInterface $adapter,
        private ContractSheetMapperInterface $mapper,
    ) {}

    /**
     * The contract tracking module has no independent contract register.
     * Contract references are composed from its authorized movement sheets.
     *
     * @return list<ContractEntity>
     */
    public function findAll(): array
    {
        $records = [
            ...$this->adapter->map('value-additives', $this->mapper->map(...)),
            ...$this->adapter->map('execution-deadlines', $this->mapper->map(...)),
        ];

        /** @var array<string, ContractEntity> $contractsByNumber */
        $contractsByNumber = [];

        foreach ($records as $record) {
            $key = (new ContractNumberValueObject($record->contractNumber))->equivalenceKey();

            $contractsByNumber[$key] = isset($contractsByNumber[$key])
                ? $this->merge($contractsByNumber[$key], $record)
                : $record;
        }

        return array_values($contractsByNumber);
    }

    private function merge(ContractEntity $current, ContractEntity $candidate): ContractEntity
    {
        return new ContractEntity(
            contractNumber: $current->contractNumber,
            company: $current->company ?? $candidate->company,
            seiProcess: $current->seiProcess ?? $candidate->seiProcess,
            municipalities: array_values(array_unique([
                ...$current->municipalities,
                ...$candidate->municipalities,
            ])),
            object: $current->object ?? $candidate->object,
            initialValue: $current->initialValue ?? $candidate->initialValue,
            updatedValue: $current->updatedValue ?? $candidate->updatedValue,
            validityStartDate: $current->validityStartDate ?? $candidate->validityStartDate,
            validityEndDate: $current->validityEndDate ?? $candidate->validityEndDate,
            executionDeadline: $current->executionDeadline ?? $candidate->executionDeadline,
            currentSituation: $current->currentSituation ?? $candidate->currentSituation,
        );
    }
}
