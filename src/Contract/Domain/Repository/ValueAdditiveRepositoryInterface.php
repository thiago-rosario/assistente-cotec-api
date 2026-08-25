<?php

declare(strict_types=1);

namespace App\Contract\Domain\Repository;

use App\Contract\Domain\Entity\ValueAdditiveEntity;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;

interface ValueAdditiveRepositoryInterface
{
    /**
     * @return list<ValueAdditiveEntity>
     */
    public function findByMunicipality(MunicipalityValueObject $municipality): array;

    /**
     * @return list<ValueAdditiveEntity>
     */
    public function findByContractNumber(ContractNumberValueObject $contractNumber): array;

    /**
     * @return list<ValueAdditiveEntity>
     */
    public function findByMunicipalityAndContractNumber(
        MunicipalityValueObject $municipality,
        ContractNumberValueObject $contractNumber,
    ): array;
}
