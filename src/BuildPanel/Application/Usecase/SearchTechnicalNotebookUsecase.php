<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\Usecase;

use App\BuildPanel\Application\DTO\SearchTechnicalNotebookInputDTO;
use App\BuildPanel\Application\DTO\SearchTechnicalNotebookOutputDTO;
use App\BuildPanel\Application\Interfaces\Usecase\SearchTechnicalNotebookUsecaseInterface;
use App\BuildPanel\Application\Trait\NullableResultTrait;
use App\BuildPanel\Domain\Entity\TechnicalNotebookEntity;
use App\BuildPanel\Domain\Repository\TechnicalNotebookRepositoryInterface;

class SearchTechnicalNotebookUsecase implements SearchTechnicalNotebookUsecaseInterface
{
    use NullableResultTrait;

    public function __construct(
        private readonly TechnicalNotebookRepositoryInterface $repository,
    ) {}

    public function __invoke(SearchTechnicalNotebookInputDTO $input): SearchTechnicalNotebookOutputDTO
    {
        $results = match (true) {
            filled($input->process) => $this->nullableResult($this->repository->findByProcess($input->process)),
            filled($input->municipality) => $this->repository->findByMunicipality($input->municipality),
            filled($input->force) => $this->repository->findByForce($input->force),
            filled($input->buildStatus) => $this->repository->findByBuildStatus($input->buildStatus),
            filled($input->term) => $this->repository->search($input->term),
            default => $this->repository->all(),
        };

        $data = array_map(
            fn (TechnicalNotebookEntity $technicalNotebook): array => get_object_vars($technicalNotebook),
            $results,
        );

        return new SearchTechnicalNotebookOutputDTO(
            term: $input->term,
            total: count($data),
            data: $data,
        );
    }
}
