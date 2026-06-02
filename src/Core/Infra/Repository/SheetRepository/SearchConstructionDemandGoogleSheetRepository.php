<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\ConstructionDemandEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;
use App\Core\Infra\Trait\SearchableEntityMatcherTrait;

class SearchConstructionDemandGoogleSheetRepository
{
    use HandlesGoogleSheetRows;
    use SearchableEntityMatcherTrait;

    /**
     * @param  list<ConstructionDemandEntity>  $constructionDemands
     * @return list<ConstructionDemandEntity>
     */
    public function search(array $constructionDemands, string $term): array
    {
        $normalizedTerm = $this->normalize($term);

        return array_values(array_filter(
            $constructionDemands,
            fn (ConstructionDemandEntity $constructionDemand): bool => $this->matchesSearchTerm($constructionDemand, $normalizedTerm),
        ));
    }
}
