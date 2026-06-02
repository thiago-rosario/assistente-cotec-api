<?php

declare(strict_types=1);

namespace App\Core\Application\Usecase;

use App\Core\Application\DTO\SearchTravelItineraryInputDTO;
use App\Core\Application\DTO\SearchTravelItineraryOutputDTO;
use App\Core\Application\Interfaces\SearchTravelItineraryUsecaseInterface;
use App\Core\Application\Trait\NullableResultTrait;
use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Domain\Repository\TravelItineraryRepositoryInterface;

class SearchTravelItineraryUsecase implements SearchTravelItineraryUsecaseInterface
{
    use NullableResultTrait;

    public function __construct(
        private readonly TravelItineraryRepositoryInterface $repository,
    ) {}

    public function __invoke(SearchTravelItineraryInputDTO $input): SearchTravelItineraryOutputDTO
    {
        $results = match (true) {
            filled($input->process) => $this->nullableResult($this->repository->findByProcess($input->process)),
            filled($input->municipality) => $this->repository->findByMunicipality($input->municipality),
            filled($input->force) => $this->repository->findByForce($input->force),
            filled($input->region) => $this->repository->findByRegion($input->region),
            filled($input->landStatus) => $this->repository->findByLandStatus($input->landStatus),
            filled($input->progress) => $this->repository->findByProgress($input->progress),
            filled($input->requester) => $this->repository->findByRequester($input->requester),
            filled($input->term) => $this->repository->search($input->term),
            default => $this->repository->all(),
        };

        $data = array_map(
            fn (TravelItineraryEntity $travelItinerary): array => get_object_vars($travelItinerary),
            $results,
        );

        return new SearchTravelItineraryOutputDTO(
            term: $input->term,
            total: count($data),
            data: $data,
        );
    }
}
