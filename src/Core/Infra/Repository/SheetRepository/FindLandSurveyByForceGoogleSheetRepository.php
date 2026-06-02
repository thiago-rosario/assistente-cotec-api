<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindLandSurveyByForceGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<LandSurveyEntity>  $landSurveys
     * @return list<LandSurveyEntity>
     */
    public function findByForce(array $landSurveys, string $force): array
    {
        $normalizedForce = $this->normalize($force);

        return array_values(array_filter(
            $landSurveys,
            fn (LandSurveyEntity $landSurvey): bool => $landSurvey->force !== null
                && $this->normalize($landSurvey->force) === $normalizedForce,
        ));
    }
}
