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
        $normalizedProcess = $this->normalize($process);

        foreach ($travelItineraries as $travelItinerary) {
            if ($travelItinerary->process !== null && $this->normalize($travelItinerary->process) === $normalizedProcess) {
                return $travelItinerary;
            }
        }

        return null;
    }
}
