<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\ConstructionDemandEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindConstructionDemandByRegionGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<ConstructionDemandEntity>  $constructionDemands
     * @return list<ConstructionDemandEntity>
     */
    public function findByRegion(array $constructionDemands, string $region): array
    {
        $normalizedRegion = $this->normalize($region);

        return array_values(array_filter(
            $constructionDemands,
            fn (ConstructionDemandEntity $constructionDemand): bool => $constructionDemand->region !== null
                && $this->normalize($constructionDemand->region) === $normalizedRegion,
        ));
    }
}
