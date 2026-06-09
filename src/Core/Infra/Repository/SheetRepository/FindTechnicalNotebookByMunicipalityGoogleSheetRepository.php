<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\TechnicalNotebookEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindTechnicalNotebookByMunicipalityGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<TechnicalNotebookEntity>  $technicalNotebooks
     * @return list<TechnicalNotebookEntity>
     */
    public function findByMunicipality(array $technicalNotebooks, string $municipality): array
    {
        return array_values(array_filter(
            $technicalNotebooks,
            fn (TechnicalNotebookEntity $technicalNotebook): bool => $this->municipalitiesMatch($technicalNotebook->municipality, $municipality),
        ));
    }
}
