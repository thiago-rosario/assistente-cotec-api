<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindLandSurveyByLandStatusGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<LandSurveyEntity>  $landSurveys
     * @return list<LandSurveyEntity>
     */
    public function findByLandStatus(array $landSurveys, string $status): array
    {
        $normalizedStatus = $this->normalize($status);

        return array_values(array_filter(
            $landSurveys,
            fn (LandSurveyEntity $landSurvey): bool => $landSurvey->landStatus !== null
                && $this->normalize($landSurvey->landStatus) === $normalizedStatus,
        ));
    }
}
