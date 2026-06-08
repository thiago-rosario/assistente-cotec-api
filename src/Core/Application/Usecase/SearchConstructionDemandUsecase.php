<?php

declare(strict_types=1);

namespace App\Core\Application\Usecase;

use App\Core\Application\DTO\SearchConstructionDemandInputDTO;
use App\Core\Application\DTO\SearchConstructionDemandOutputDTO;
use App\Core\Application\Interfaces\Usecase\SearchConstructionDemandUsecaseInterface;
use App\Core\Application\Trait\NullableResultTrait;
use App\Core\Domain\Entity\ConstructionDemandEntity;
use App\Core\Domain\Repository\ConstructionDemandRepositoryInterface;

class SearchConstructionDemandUsecase implements SearchConstructionDemandUsecaseInterface
{
    use NullableResultTrait;

    public function __construct(
        private readonly ConstructionDemandRepositoryInterface $repository,
    ) {}

    public function __invoke(SearchConstructionDemandInputDTO $input): SearchConstructionDemandOutputDTO
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
            fn (ConstructionDemandEntity $constructionDemand): array => get_object_vars($constructionDemand),
            $results,
        );

        return new SearchConstructionDemandOutputDTO(
            term: $input->term,
            total: count($data),
            data: $data,
        );
    }
}
