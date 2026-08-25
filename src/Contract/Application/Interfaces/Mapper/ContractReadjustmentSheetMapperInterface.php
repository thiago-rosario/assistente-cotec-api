<?php

declare(strict_types=1);

namespace App\Contract\Application\Interfaces\Mapper;

use App\Contract\Domain\Entity\ContractReadjustmentEntity;

interface ContractReadjustmentSheetMapperInterface
{
    public function map(array $row): ContractReadjustmentEntity;
}
