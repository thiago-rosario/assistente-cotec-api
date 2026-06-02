<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\ConstructionDemandEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindConstructionDemandByProgressGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<ConstructionDemandEntity>  $constructionDemands
     * @return list<ConstructionDemandEntity>
     */
    public function findByProgress(array $constructionDemands, string $progress): array
    {
        $normalizedProgress = $this->normalize($progress);

        return array_values(array_filter(
            $constructionDemands,
            fn (ConstructionDemandEntity $constructionDemand): bool => $constructionDemand->progress !== null
                && $this->normalize($constructionDemand->progress) === $normalizedProgress,
        ));
    }
}
