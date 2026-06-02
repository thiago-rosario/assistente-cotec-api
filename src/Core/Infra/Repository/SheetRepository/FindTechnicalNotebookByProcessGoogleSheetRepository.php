<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\TechnicalNotebookEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindTechnicalNotebookByProcessGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<TechnicalNotebookEntity>  $technicalNotebooks
     */
    public function findByProcess(array $technicalNotebooks, string $process): ?TechnicalNotebookEntity
    {
        $normalizedProcess = $this->normalize($process);

        foreach ($technicalNotebooks as $technicalNotebook) {
            if ($technicalNotebook->process !== null && $this->normalize($technicalNotebook->process) === $normalizedProcess) {
                return $technicalNotebook;
            }
        }

        return null;
    }
}
