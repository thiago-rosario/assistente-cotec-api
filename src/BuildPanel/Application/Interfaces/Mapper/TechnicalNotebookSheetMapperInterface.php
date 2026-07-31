<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\Interfaces\Mapper;

use App\BuildPanel\Domain\Entity\TechnicalNotebookEntity;

interface TechnicalNotebookSheetMapperInterface
{
    public function fromRow(array $row): TechnicalNotebookEntity;
}
