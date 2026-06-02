<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces;

use App\Core\Application\DTO\SearchLandSurveyInputDTO;
use App\Core\Application\DTO\SearchLandSurveyOutputDTO;

interface SearchLandSurveyUsecaseInterface
{
    public function __invoke(SearchLandSurveyInputDTO $input): SearchLandSurveyOutputDTO;
}
