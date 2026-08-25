<?php

declare(strict_types=1);

namespace App\Contract\Domain\Repository;

use App\Contract\Domain\Entity\ContractEntity;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;

interface ContractRepositoryInterface
{
    public function findByContractNumber(ContractNumberValueObject $contractNumber): ?ContractEntity;

    public function findBySeiProcess(string $seiProcess): ?ContractEntity;

    /**
     * @return list<ContractEntity>
     */
    public function findByMunicipality(MunicipalityValueObject $municipality): array;

    /**
     * @return list<ContractEntity>
     */
    public function findByCompany(string $company): array;
}
