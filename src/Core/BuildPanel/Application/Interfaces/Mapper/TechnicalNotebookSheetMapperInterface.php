<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Application\Interfaces\Mapper;

use App\Core\BuildPanel\Domain\Entity\TechnicalNotebookEntity;

interface TechnicalNotebookSheetMapperInterface
{
    public function fromRow(array $row): TechnicalNotebookEntity;
}
