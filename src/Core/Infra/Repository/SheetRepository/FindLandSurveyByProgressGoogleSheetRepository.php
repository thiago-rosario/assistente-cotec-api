<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindLandSurveyByProgressGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<LandSurveyEntity>  $landSurveys
     * @return list<LandSurveyEntity>
     */
    public function findByProgress(array $landSurveys, string $progress): array
    {
        $normalizedProgress = $this->normalize($progress);

        return array_values(array_filter(
            $landSurveys,
            fn (LandSurveyEntity $landSurvey): bool => $landSurvey->progress !== null
                && $this->normalize($landSurvey->progress) === $normalizedProgress,
        ));
    }
}
