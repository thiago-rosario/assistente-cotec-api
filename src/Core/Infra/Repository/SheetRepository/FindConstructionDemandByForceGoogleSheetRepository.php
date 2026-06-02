<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\ConstructionDemandEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindConstructionDemandByForceGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<ConstructionDemandEntity>  $constructionDemands
     * @return list<ConstructionDemandEntity>
     */
    public function findByForce(array $constructionDemands, string $force): array
    {
        $normalizedForce = $this->normalize($force);

        return array_values(array_filter(
            $constructionDemands,
            fn (ConstructionDemandEntity $constructionDemand): bool => $constructionDemand->force !== null
                && $this->normalize($constructionDemand->force) === $normalizedForce,
        ));
    }
}
