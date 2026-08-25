<?php

declare(strict_types=1);

namespace App\Contract\Application\Interfaces\Mapper;

use App\Contract\Domain\Entity\ContractEntity;

interface ContractSheetMapperInterface
{
    public function map(array $row): ContractEntity;
}
