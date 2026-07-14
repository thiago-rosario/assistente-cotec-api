<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Infra\Repository\SheetRepository;

use App\Core\BuildPanel\Domain\Entity\TechnicalNotebookEntity;
use App\Core\BuildPanel\Infra\Trait\HandlesGoogleSheetRows;

class FindTechnicalNotebookByBuildStatusGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<TechnicalNotebookEntity>  $technicalNotebooks
     * @return list<TechnicalNotebookEntity>
     */
    public function findByBuildStatus(array $technicalNotebooks, string $status): array
    {
        $normalizedStatus = $this->normalize($status);

        return array_values(array_filter(
            $technicalNotebooks,
            fn (TechnicalNotebookEntity $technicalNotebook): bool => $technicalNotebook->buildStatus !== null
                && $this->normalize($technicalNotebook->buildStatus) === $normalizedStatus,
        ));
    }
}
