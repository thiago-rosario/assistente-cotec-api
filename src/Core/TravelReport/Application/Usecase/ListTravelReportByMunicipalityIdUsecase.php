<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\Usecase;

use App\Core\TravelReport\Application\DTO\ListTravelReportByMunicipalityIdInputDTO;
use App\Core\TravelReport\Application\DTO\ListTravelReportsOutputDTO;
use App\Core\TravelReport\Application\DTO\PersistTravelReportOutputDTO;
use App\Core\TravelReport\Application\Interface\Usecase\ListTravelReportByMunicipalityIdUsecaseInterface;
use App\Core\TravelReport\Application\ToOutputTrait;
use App\Core\TravelReport\Domain\Entity\TravelReportEntity;
use App\Core\TravelReport\Domain\Repository\TravelReportRepositoryInterface;

class ListTravelReportByMunicipalityIdUsecase implements ListTravelReportByMunicipalityIdUsecaseInterface
{
    use ToOutputTrait;

    public function __construct(
        private readonly TravelReportRepositoryInterface $repository,
    ) {}

    public function __invoke(ListTravelReportByMunicipalityIdInputDTO $dto): ListTravelReportsOutputDTO
    {
        $data = array_map(
            fn (TravelReportEntity $travelReport): PersistTravelReportOutputDTO => $this->toOutput($travelReport),
            $this->repository->findByMunicipalityId($dto->municipalityId),
        );

        return new ListTravelReportsOutputDTO(
            total: count($data),
            data: $data,
        );
    }
}
