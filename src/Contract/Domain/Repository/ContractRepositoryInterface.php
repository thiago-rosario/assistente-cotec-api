<?php

declare(strict_types=1);

namespace App\Contract\Domain\Repository;

use App\Contract\Domain\Entity\ContractEntity;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;

interface ContractRepositoryInterface
{
    public function findByContractNumber(ContractNumberValueObject $contractNumber): ?ContractEntity;

    public function findBySeiProcess(string $seiProcess): ?ContractEntity;
}
