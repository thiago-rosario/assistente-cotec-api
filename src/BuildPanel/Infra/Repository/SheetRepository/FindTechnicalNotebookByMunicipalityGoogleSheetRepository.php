<?php

declare(strict_types=1);

namespace App\BuildPanel\Infra\Repository\SheetRepository;

use App\BuildPanel\Domain\Entity\TechnicalNotebookEntity;
use App\BuildPanel\Infra\Trait\HandlesGoogleSheetRows;

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
