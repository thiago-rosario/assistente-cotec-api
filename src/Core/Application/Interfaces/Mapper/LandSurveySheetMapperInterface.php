<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Mapper;

use App\Core\Domain\Entity\LandSurveyEntity;

interface LandSurveySheetMapperInterface
{
    public function fromRow(array $row): LandSurveyEntity;
}
