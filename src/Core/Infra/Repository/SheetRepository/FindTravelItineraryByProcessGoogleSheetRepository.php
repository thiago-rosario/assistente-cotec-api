<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindTravelItineraryByProcessGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<TravelItineraryEntity>  $travelItineraries
     */
    public function findByProcess(array $travelItineraries, string $process): ?TravelItineraryEntity
    {
        foreach ($travelItineraries as $travelItinerary) {
            if ($this->processesMatch($travelItinerary->process, $process)) {
                return $travelItinerary;
            }
        }

        return null;
    }
}
