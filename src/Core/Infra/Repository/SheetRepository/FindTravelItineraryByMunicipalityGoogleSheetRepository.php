<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindTravelItineraryByMunicipalityGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<TravelItineraryEntity>  $travelItineraries
     * @return list<TravelItineraryEntity>
     */
    public function findByMunicipality(array $travelItineraries, string $municipality): array
    {
        $normalizedMunicipality = $this->normalize($municipality);

        return array_values(array_filter(
            $travelItineraries,
            fn (TravelItineraryEntity $travelItinerary): bool => $this->normalize($travelItinerary->municipality) === $normalizedMunicipality,
        ));
    }
}
