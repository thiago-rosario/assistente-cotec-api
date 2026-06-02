<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;
use App\Core\Infra\Trait\SearchableEntityMatcherTrait;

class SearchLandSurveyGoogleSheetRepository
{
    use HandlesGoogleSheetRows;
    use SearchableEntityMatcherTrait;

    /**
     * @param  list<LandSurveyEntity>  $landSurveys
     * @return list<LandSurveyEntity>
     */
    public function search(array $landSurveys, string $term): array
    {
        $normalizedTerm = $this->normalize($term);

        return array_values(array_filter(
            $landSurveys,
            fn (LandSurveyEntity $landSurvey): bool => $this->matchesSearchTerm($landSurvey, $normalizedTerm),
        ));
    }
}
