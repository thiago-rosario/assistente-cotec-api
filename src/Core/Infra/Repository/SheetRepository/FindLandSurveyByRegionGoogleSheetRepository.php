<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindLandSurveyByRegionGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<LandSurveyEntity>  $landSurveys
     * @return list<LandSurveyEntity>
     */
    public function findByRegion(array $landSurveys, string $region): array
    {
        $normalizedRegion = $this->normalize($region);

        return array_values(array_filter(
            $landSurveys,
            fn (LandSurveyEntity $landSurvey): bool => $landSurvey->region !== null
                && $this->normalize($landSurvey->region) === $normalizedRegion,
        ));
    }
}
