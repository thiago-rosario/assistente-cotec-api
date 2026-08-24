<?php

declare(strict_types=1);

namespace App\Contract\Domain\Repository;

use App\Contract\Domain\Entity\ContractExecutionDeadlineEntity;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;

interface ContractExecutionDeadlineRepositoryInterface
{
    /**
     * @return list<ContractExecutionDeadlineEntity>
     */
    public function findByContractNumber(ContractNumberValueObject $contractNumber): array;
}
