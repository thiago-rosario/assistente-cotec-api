<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindTravelItineraryByRegionGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<TravelItineraryEntity>  $travelItineraries
     * @return list<TravelItineraryEntity>
     */
    public function findByRegion(array $travelItineraries, string $region): array
    {
        $normalizedRegion = $this->normalize($region);

        return array_values(array_filter(
            $travelItineraries,
            fn (TravelItineraryEntity $travelItinerary): bool => $travelItinerary->region !== null
                && $this->normalize($travelItinerary->region) === $normalizedRegion,
        ));
    }
}
