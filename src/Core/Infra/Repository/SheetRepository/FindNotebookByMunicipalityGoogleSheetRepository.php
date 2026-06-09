<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\NotebookEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindNotebookByMunicipalityGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<NotebookEntity>  $notebooks
     * @return list<NotebookEntity>
     */
    public function findByMunicipality(array $notebooks, string $municipality): array
    {
        return array_values(array_filter(
            $notebooks,
            fn (NotebookEntity $notebook): bool => $this->municipalitiesMatch($notebook->municipality, $municipality),
        ));
    }
}
