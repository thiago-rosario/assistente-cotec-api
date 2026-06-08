<?php

declare(strict_types=1);

namespace App\Core\Infra\Adapter;

use App\Core\Application\Interfaces\Adapter\SearchConstructionDemandAdapterInterface;
use App\Core\Application\Interfaces\Adapter\SearchLandSurveyAdapterInterface;
use App\Core\Application\Interfaces\Adapter\SearchTechnicalNotebookAdapterInterface;
use App\Core\Application\Interfaces\Adapter\SearchTravelItineraryAdapterInterface;
use App\Core\Application\Interfaces\Adapter\WhatsappMessageSearchAdapterInterface;
use App\Core\Application\Interfaces\Usecase\SearchConstructionDemandUsecaseInterface;
use App\Core\Application\Interfaces\Usecase\SearchLandSurveyUsecaseInterface;
use App\Core\Application\Interfaces\Usecase\SearchTechnicalNotebookUsecaseInterface;
use App\Core\Application\Interfaces\Usecase\SearchTravelItineraryUsecaseInterface;

class WhatsappMessageSearchAdapter implements WhatsappMessageSearchAdapterInterface
{
    public function __construct(
        private readonly SearchTechnicalNotebookUsecaseInterface $searchTechnicalNotebook,
        private readonly SearchConstructionDemandUsecaseInterface $searchConstructionDemand,
        private readonly SearchLandSurveyUsecaseInterface $searchLandSurvey,
        private readonly SearchTravelItineraryUsecaseInterface $searchTravelItinerary,
        private readonly SearchTechnicalNotebookAdapterInterface $technicalNotebookAdapter,
        private readonly SearchConstructionDemandAdapterInterface $constructionDemandAdapter,
        private readonly SearchLandSurveyAdapterInterface $landSurveyAdapter,
        private readonly SearchTravelItineraryAdapterInterface $travelItineraryAdapter,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array{term: string|null, total: int, data: list<array<string, mixed>>}
     */
    public function search(string $intent, array $filters): array
    {
        return match ($intent) {
            'search_technical_notebook' => $this->technicalNotebookAdapter->toArray(
                ($this->searchTechnicalNotebook)($this->technicalNotebookAdapter->fromArray($filters)),
            ),
            'search_construction_demand' => $this->constructionDemandAdapter->toArray(
                ($this->searchConstructionDemand)($this->constructionDemandAdapter->fromArray($filters)),
            ),
            'search_land_survey' => $this->landSurveyAdapter->toArray(
                ($this->searchLandSurvey)($this->landSurveyAdapter->fromArray($filters)),
            ),
            'search_travel_itinerary' => $this->travelItineraryAdapter->toArray(
                ($this->searchTravelItinerary)($this->travelItineraryAdapter->fromArray($filters)),
            ),
            default => $this->emptyResult(),
        };
    }

    /**
     * @return array{term: string|null, total: int, data: list<array<string, mixed>>}
     */
    private function emptyResult(): array
    {
        return [
            'term' => null,
            'total' => 0,
            'data' => [],
        ];
    }
}
