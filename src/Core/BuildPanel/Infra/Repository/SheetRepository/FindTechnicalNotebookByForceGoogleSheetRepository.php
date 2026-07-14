<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Infra\Repository\SheetRepository;

use App\Core\BuildPanel\Domain\Entity\TechnicalNotebookEntity;
use App\Core\BuildPanel\Infra\Trait\HandlesGoogleSheetRows;

class FindTechnicalNotebookByForceGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<TechnicalNotebookEntity>  $technicalNotebooks
     * @return list<TechnicalNotebookEntity>
     */
    public function findByForce(array $technicalNotebooks, string $force): array
    {
        $normalizedForce = $this->normalize($force);

        return array_values(array_filter(
            $technicalNotebooks,
            fn (TechnicalNotebookEntity $technicalNotebook): bool => $technicalNotebook->force !== null
                && $this->normalize($technicalNotebook->force) === $normalizedForce,
        ));
    }
}
