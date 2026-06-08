<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Mapper;

use App\Core\Domain\Entity\TechnicalNotebookEntity;

interface TechnicalNotebookSheetMapperInterface
{
    public function fromRow(array $row): TechnicalNotebookEntity;
}
