<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindLandSurveyByMunicipalityGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<LandSurveyEntity>  $landSurveys
     * @return list<LandSurveyEntity>
     */
    public function findByMunicipality(array $landSurveys, string $municipality): array
    {
        $normalizedMunicipality = $this->normalize($municipality);

        return array_values(array_filter(
            $landSurveys,
            fn (LandSurveyEntity $landSurvey): bool => $this->normalize($landSurvey->municipality) === $normalizedMunicipality,
        ));
    }
}
