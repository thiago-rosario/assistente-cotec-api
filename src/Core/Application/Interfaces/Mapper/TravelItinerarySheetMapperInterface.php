<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Mapper;

use App\Core\Domain\Entity\TravelItineraryEntity;

interface TravelItinerarySheetMapperInterface
{
    public function fromRow(array $row): TravelItineraryEntity;
}
