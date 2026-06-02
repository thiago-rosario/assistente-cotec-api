<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces;

use App\Core\Domain\Entity\ConstructionDemandEntity;

interface ConstructionDemandSheetMapperInterface
{
    public function fromRow(array $row): ConstructionDemandEntity;
}
