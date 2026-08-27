<?php

declare(strict_types=1);

namespace App\Contract\Infra\Repository\SheetRepository;

use App\Contract\Domain\Entity\ContractEntity;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;

final class FindContractByContractNumberGoogleSheetRepository
{
    public function __construct(
        private readonly FindContractRecordsGoogleSheetRepository $recordsRepository,
    ) {}

    public function findByContractNumber(ContractNumberValueObject $contractNumber): ?ContractEntity
    {
        $contracts = $this->recordsRepository->findAll();

        foreach ($contracts as $contract) {
            if ((new ContractNumberValueObject($contract->contractNumber))->equals($contractNumber)) {
                return $contract;
            }
        }

        return null;
    }
}
