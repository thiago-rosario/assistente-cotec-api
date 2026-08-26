<?php

declare(strict_types=1);

namespace App\Contract\Infra\Repository\SheetRepository;

use App\Contract\Application\Interfaces\Adapter\ContractSheetAdapterInterface;
use App\Contract\Application\Interfaces\Mapper\ContractReadjustmentSheetMapperInterface;
use App\Contract\Domain\Entity\ContractReadjustmentEntity;
use App\Contract\Domain\Repository\ContractReadjustmentRepositoryInterface;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;

final class ContractReadjustmentGoogleSheetRepository implements ContractReadjustmentRepositoryInterface
{
    public function __construct(
        private readonly ContractSheetAdapterInterface $adapter,
        private readonly ContractReadjustmentSheetMapperInterface $mapper,
    ) {}

    /**
     * @return list<ContractReadjustmentEntity>
     */
    public function findByContractNumber(ContractNumberValueObject $contractNumber): array
    {
        $readjustments = $this->adapter->map('readjustments', $this->mapper->map(...));

        return array_values(array_filter(
            $readjustments,
            fn (ContractReadjustmentEntity $readjustment): bool => $readjustment->contractNumber === $contractNumber->value,
        ));
    }
}
