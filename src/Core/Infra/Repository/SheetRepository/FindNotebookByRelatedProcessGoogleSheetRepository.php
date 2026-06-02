<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\NotebookEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindNotebookByRelatedProcessGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<NotebookEntity>  $notebooks
     */
    public function findByRelatedProcess(array $notebooks, string $process): ?NotebookEntity
    {
        foreach ($notebooks as $notebook) {
            if ($this->processesMatch($notebook->relatedProcess, $process)) {
                return $notebook;
            }
        }

        return null;
    }
}
