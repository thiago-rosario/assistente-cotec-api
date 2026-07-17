<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\Usecase;

use App\Core\TravelReport\Application\DTO\ListTravelReportsInputDTO;
use App\Core\TravelReport\Application\DTO\ListTravelReportsOutputDTO;
use App\Core\TravelReport\Application\DTO\PersistTravelReportOutputDTO;
use App\Core\TravelReport\Application\Interface\Usecase\ListTravelReportsUsecaseInterface;
use App\Core\TravelReport\Application\ToOutputTrait;
use App\Core\TravelReport\Domain\Entity\TravelReportEntity;
use App\Core\TravelReport\Domain\Repository\TravelReportRepositoryInterface;

class ListTravelReportsUsecase implements ListTravelReportsUsecaseInterface
{
    use ToOutputTrait;

    public function __construct(
        private readonly TravelReportRepositoryInterface $repository,
    ) {}

    public function __invoke(ListTravelReportsInputDTO $dto): ListTravelReportsOutputDTO
    {
        $data = array_map(
            fn (TravelReportEntity $travelReport): PersistTravelReportOutputDTO => $this->toOutput($travelReport),
            $this->repository->all(),
        );

        return new ListTravelReportsOutputDTO(
            total: count($data),
            data: $data,
        );
    }

}
