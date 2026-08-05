<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\Usecase;

use App\TechnicalInspectionReport\Application\DTO\SearchTechnicalInspectionReportCatalogInputDTO;
use App\TechnicalInspectionReport\Application\Interfaces\Usecase\FindTechnicalInspectionReportUsecaseInterface;
use App\TechnicalInspectionReport\Application\Trait\FindByReportIdTrait;
use App\TechnicalInspectionReport\Domain\Repository\TechnicalInspectionReportSheetRepositoryInterface;

class FindTechnicalInspectionReportUsecase implements FindTechnicalInspectionReportUsecaseInterface
{
    use FindByReportIdTrait;

    public function __construct(
        private readonly TechnicalInspectionReportSheetRepositoryInterface $repository,
    ) {}

    public function __invoke(SearchTechnicalInspectionReportCatalogInputDTO $input): array
    {
        return match (true) {
            filled($input->reportId) => $this->findByReportId($input),
            filled($input->municipality) => $this->repository->findByMunicipality($input),
            filled($input->seiProcess) => $this->repository->findByProcessSei($input),
            default => [],
        };
    }
}
