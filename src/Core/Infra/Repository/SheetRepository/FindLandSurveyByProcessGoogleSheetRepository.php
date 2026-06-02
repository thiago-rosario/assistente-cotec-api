<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindLandSurveyByProcessGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<LandSurveyEntity>  $landSurveys
     */
    public function findByProcess(array $landSurveys, string $process): ?LandSurveyEntity
    {
        $normalizedProcess = $this->normalize($process);

        foreach ($landSurveys as $landSurvey) {
            if ($landSurvey->process !== null && $this->normalize($landSurvey->process) === $normalizedProcess) {
                return $landSurvey;
            }
        }

        return null;
    }
}
