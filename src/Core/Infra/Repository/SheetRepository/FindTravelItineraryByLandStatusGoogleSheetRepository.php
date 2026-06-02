<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindTravelItineraryByLandStatusGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<TravelItineraryEntity>  $travelItineraries
     * @return list<TravelItineraryEntity>
     */
    public function findByLandStatus(array $travelItineraries, string $status): array
    {
        $normalizedStatus = $this->normalize($status);

        return array_values(array_filter(
            $travelItineraries,
            fn (TravelItineraryEntity $travelItinerary): bool => $travelItinerary->landStatus !== null
                && $this->normalize($travelItinerary->landStatus) === $normalizedStatus,
        ));
    }
}
