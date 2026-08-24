<?php

declare(strict_types=1);

namespace App\Contract\Domain\Repository;

use App\Contract\Domain\Entity\ContractReadjustmentEntity;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;

interface ContractReadjustmentRepositoryInterface
{
    /**
     * @return list<ContractReadjustmentEntity>
     */
    public function findByContractNumber(ContractNumberValueObject $contractNumber): array;
}
