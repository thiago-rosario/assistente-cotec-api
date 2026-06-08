<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Usecase;

use App\Core\Application\DTO\SearchTravelItineraryInputDTO;
use App\Core\Application\DTO\SearchTravelItineraryOutputDTO;

interface SearchTravelItineraryUsecaseInterface
{
    public function __invoke(SearchTravelItineraryInputDTO $input): SearchTravelItineraryOutputDTO;
}
