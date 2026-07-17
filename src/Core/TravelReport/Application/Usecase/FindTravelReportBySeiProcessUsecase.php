<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\Usecase;

use App\Core\TravelReport\Application\DTO\FindTravelReportBySeiProcessInputDTO;
use App\Core\TravelReport\Application\DTO\FindTravelReportBySeiProcessOutputDTO;
use App\Core\TravelReport\Application\DTO\PersistTravelReportOutputDTO;
use App\Core\TravelReport\Application\Interface\Usecase\FindTravelReportBySeiProcessUsecaseInterface;
use App\Core\TravelReport\Application\ToOutputTrait;
use App\Core\TravelReport\Domain\Entity\TravelReportEntity;
use App\Core\TravelReport\Domain\Repository\TravelReportRepositoryInterface;

class FindTravelReportBySeiProcessUsecase implements FindTravelReportBySeiProcessUsecaseInterface
{
    use ToOutputTrait;

    public function __construct(
        private readonly TravelReportRepositoryInterface $repository,
    ) {}

    public function __invoke(FindTravelReportBySeiProcessInputDTO $dto): FindTravelReportBySeiProcessOutputDTO
    {
        $travelReport = $this->repository->findBySeiProcess($dto->seiProcess);

        return new FindTravelReportBySeiProcessOutputDTO(
            data: $travelReport instanceof TravelReportEntity
                ? $this->toOutput($travelReport)
                : null,
        );
    }
}
