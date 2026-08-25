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
        $contracts = array_map(
            $this->mapper->map(...),
            $this->adapter->read('contracts'),
        );

        foreach ($contracts as $contract) {
            if ($contract->contractNumber === $contractNumber->value) {
                return $contract;
            }
        }

        return null;
    }
}
