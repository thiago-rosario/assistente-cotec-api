<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Infra\Repository\SheetRepository;

use App\Core\BuildPanel\Domain\Entity\TechnicalNotebookEntity;
use App\Core\BuildPanel\Infra\Trait\HandlesGoogleSheetRows;

class FindTechnicalNotebookByProcessGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<TechnicalNotebookEntity>  $technicalNotebooks
     */
    public function findByProcess(array $technicalNotebooks, string $process): ?TechnicalNotebookEntity
    {
        foreach ($technicalNotebooks as $technicalNotebook) {
            if ($this->processesMatch($technicalNotebook->process, $process)) {
                return $technicalNotebook;
            }
        }

        return null;
    }
}
