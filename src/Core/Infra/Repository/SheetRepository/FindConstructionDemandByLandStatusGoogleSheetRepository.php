<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\ConstructionDemandEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindConstructionDemandByLandStatusGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<ConstructionDemandEntity>  $constructionDemands
     * @return list<ConstructionDemandEntity>
     */
    public function findByLandStatus(array $constructionDemands, string $status): array
    {
        $normalizedStatus = $this->normalize($status);

        return array_values(array_filter(
            $constructionDemands,
            fn (ConstructionDemandEntity $constructionDemand): bool => $constructionDemand->landStatus !== null
                && $this->normalize($constructionDemand->landStatus) === $normalizedStatus,
        ));
    }
}
