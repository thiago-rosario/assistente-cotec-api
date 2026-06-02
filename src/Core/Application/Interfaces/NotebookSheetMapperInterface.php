<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces;

use App\Core\Domain\Entity\NotebookEntity;

interface NotebookSheetMapperInterface
{
    public function fromRow(array $row): NotebookEntity;
}
