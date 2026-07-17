<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\Usecase;

use App\Core\TravelReport\Application\DTO\DeleteTravelReportInputDTO;
use App\Core\TravelReport\Application\DTO\DeleteTravelReportOutputDTO;
use App\Core\TravelReport\Application\Interface\Usecase\DeleteTravelReportUsecaseInterface;
use App\Core\TravelReport\Domain\Repository\TravelReportRepositoryInterface;

class DeleteTravelReportUsecase implements DeleteTravelReportUsecaseInterface
{
    public function __construct(
        private readonly TravelReportRepositoryInterface $repository,
    ) {}

    public function __invoke(DeleteTravelReportInputDTO $dto): DeleteTravelReportOutputDTO
    {
        return new DeleteTravelReportOutputDTO(
            id: $dto->id,
            deleted: $this->repository->delete($dto->id),
        );
    }
}
