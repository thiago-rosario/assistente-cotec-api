<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;
use App\Core\Infra\Trait\SearchableEntityMatcherTrait;

class SearchTravelItineraryGoogleSheetRepository
{
    use HandlesGoogleSheetRows;
    use SearchableEntityMatcherTrait;

    /**
     * @param  list<TravelItineraryEntity>  $travelItineraries
     * @return list<TravelItineraryEntity>
     */
    public function search(array $travelItineraries, string $term): array
    {
        $normalizedTerm = $this->normalize($term);

        return array_values(array_filter(
            $travelItineraries,
            fn (TravelItineraryEntity $travelItinerary): bool => $this->matchesSearchTerm($travelItinerary, $normalizedTerm),
        ));
    }
}
