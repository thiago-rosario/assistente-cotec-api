<?php

declare(strict_types=1);

namespace App\Contract\Application\Interfaces\Mapper;

use App\Contract\Domain\Entity\ContractExecutionDeadlineEntity;

interface ContractExecutionDeadlineSheetMapperInterface
{
    public function map(array $row): ContractExecutionDeadlineEntity;
}
