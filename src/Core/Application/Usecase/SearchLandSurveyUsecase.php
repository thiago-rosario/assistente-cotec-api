<?php

declare(strict_types=1);

namespace App\Core\Application\Usecase;

use App\Core\Application\DTO\SearchLandSurveyInputDTO;
use App\Core\Application\DTO\SearchLandSurveyOutputDTO;
use App\Core\Application\Interfaces\SearchLandSurveyUsecaseInterface;
use App\Core\Application\Trait\NullableResultTrait;
use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Domain\Repository\LandSurveyRepositoryInterface;

class SearchLandSurveyUsecase implements SearchLandSurveyUsecaseInterface
{
    use NullableResultTrait;

    public function __construct(
        private readonly LandSurveyRepositoryInterface $repository,
    ) {}

    public function __invoke(SearchLandSurveyInputDTO $input): SearchLandSurveyOutputDTO
    {
        $results = match (true) {
            filled($input->process) => $this->nullableResult($this->repository->findByProcess($input->process)),
            filled($input->municipality) => $this->repository->findByMunicipality($input->municipality),
            filled($input->force) => $this->repository->findByForce($input->force),
            filled($input->region) => $this->repository->findByRegion($input->region),
            filled($input->landStatus) => $this->repository->findByLandStatus($input->landStatus),
            filled($input->progress) => $this->repository->findByProgress($input->progress),
            filled($input->term) => $this->repository->search($input->term),
            default => $this->repository->all(),
        };

        $data = array_map(
            fn (LandSurveyEntity $landSurvey): array => get_object_vars($landSurvey),
            $results,
        );

        return new SearchLandSurveyOutputDTO(
            term: $input->term,
            total: count($data),
            data: $data,
        );
    }
}
