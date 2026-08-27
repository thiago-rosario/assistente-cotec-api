<?php

declare(strict_types=1);

namespace App\Contract\Infra\Repository\SheetRepository;

use App\Contract\Application\Interfaces\Adapter\ContractSheetAdapterInterface;
use App\Contract\Application\Interfaces\Mapper\ContractSheetMapperInterface;
use App\Contract\Domain\Entity\ContractEntity;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;

final class FindContractByContractNumberGoogleSheetRepository
{
    public function __construct(
        private readonly ContractSheetAdapterInterface $adapter,
        private readonly ContractSheetMapperInterface $mapper,
    ) {}

    public function findByContractNumber(ContractNumberValueObject $contractNumber): ?ContractEntity
    {
        $contracts = $this->adapter->map('contracts', $this->mapper->map(...));

        foreach ($contracts as $contract) {
            if ((new ContractNumberValueObject($contract->contractNumber))->equals($contractNumber)) {
                return $contract;
            }
        }

        return null;
    }
}
