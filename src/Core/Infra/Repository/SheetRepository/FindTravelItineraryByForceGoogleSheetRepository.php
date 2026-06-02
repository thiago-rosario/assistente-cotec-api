<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindTravelItineraryByForceGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<TravelItineraryEntity>  $travelItineraries
     * @return list<TravelItineraryEntity>
     */
    public function findByForce(array $travelItineraries, string $force): array
    {
        $normalizedForce = $this->normalize($force);

        return array_values(array_filter(
            $travelItineraries,
            fn (TravelItineraryEntity $travelItinerary): bool => $travelItinerary->force !== null
                && $this->normalize($travelItinerary->force) === $normalizedForce,
        ));
    }
}
