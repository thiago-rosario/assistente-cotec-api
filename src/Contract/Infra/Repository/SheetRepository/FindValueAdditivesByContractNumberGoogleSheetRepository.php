<?php

declare(strict_types=1);

namespace App\Contract\Infra\Repository\SheetRepository;

use App\Contract\Application\Interfaces\Adapter\ContractSheetAdapterInterface;
use App\Contract\Application\Interfaces\Mapper\ValueAdditiveSheetMapperInterface;
use App\Contract\Domain\Entity\ValueAdditiveEntity;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;

final class FindValueAdditivesByContractNumberGoogleSheetRepository
{
    public function __construct(
        private readonly ContractSheetAdapterInterface $adapter,
        private readonly ValueAdditiveSheetMapperInterface $mapper,
    ) {}

    /**
     * @return list<ValueAdditiveEntity>
     */
    public function findByContractNumber(ContractNumberValueObject $contractNumber): array
    {
        $valueAdditives = array_map(
            $this->mapper->map(...),
            $this->adapter->read('value-additives'),
        );

        return array_values(array_filter(
            $valueAdditives,
            fn (ValueAdditiveEntity $valueAdditive): bool => $valueAdditive->contractNumber === $contractNumber->value,
        ));
    }
}
