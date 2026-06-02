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
        $normalizedProcess = $this->normalize($process);

        foreach ($notebooks as $notebook) {
            if ($notebook->relatedProcess !== null && $this->normalize($notebook->relatedProcess) === $normalizedProcess) {
                return $notebook;
            }
        }

        return null;
    }
}
