<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindTravelItineraryByRequesterGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<TravelItineraryEntity>  $travelItineraries
     * @return list<TravelItineraryEntity>
     */
    public function findByRequester(array $travelItineraries, string $requester): array
    {
        $normalizedRequester = $this->normalize($requester);

        return array_values(array_filter(
            $travelItineraries,
            fn (TravelItineraryEntity $travelItinerary): bool => $travelItinerary->requester !== null
                && $this->normalize($travelItinerary->requester) === $normalizedRequester,
        ));
    }
}
