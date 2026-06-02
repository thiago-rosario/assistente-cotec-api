<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\ConstructionDemandEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindConstructionDemandByMunicipalityGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<ConstructionDemandEntity>  $constructionDemands
     * @return list<ConstructionDemandEntity>
     */
    public function findByMunicipality(array $constructionDemands, string $municipality): array
    {
        $normalizedMunicipality = $this->normalize($municipality);

        return array_values(array_filter(
            $constructionDemands,
            fn (ConstructionDemandEntity $constructionDemand): bool => $this->normalize($constructionDemand->municipality) === $normalizedMunicipality,
        ));
    }
}
