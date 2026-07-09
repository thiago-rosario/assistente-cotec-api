<?php

declare(strict_types=1);

namespace App\BuildPanel\Infra\Repository\SheetRepository;

use App\BuildPanel\Domain\Entity\TechnicalNotebookEntity;
use App\BuildPanel\Infra\Trait\HandlesGoogleSheetRows;

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
