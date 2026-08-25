<?php

declare(strict_types=1);

namespace App\Contract\Application\Usecase;

use App\Contract\Application\DTO\ContractAdjustmentsOutputDTO;
use App\Contract\Application\DTO\ContractReadjustmentOutputDTO;
use App\Contract\Application\DTO\ContractReadjustmentsByContractOutputDTO;
use App\Contract\Application\DTO\MunicipalityContractReferenceDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;
use App\Contract\Application\Interfaces\Resolver\MunicipalityContractResolverInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractAdjustmentsUsecaseInterface;
use App\Contract\Domain\Entity\ContractReadjustmentEntity;
use App\Contract\Domain\Repository\ContractReadjustmentRepositoryInterface;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;
use App\Contract\Enum\ContractSearchTypeEnum;
use App\Contract\Exception\InvalidContractAdjustmentsSearchTypeException;

class FindContractAdjustmentsUsecase implements FindContractAdjustmentsUsecaseInterface
{
    public function __construct(
        private readonly ContractReadjustmentRepositoryInterface $repository,
        private readonly MunicipalityContractResolverInterface $resolver,
    ) {}

    public function __invoke(SearchContractInputDTO $input): ContractAdjustmentsOutputDTO
    {
        $toOutputDTO = static function (
            ContractReadjustmentEntity $adjustment,
            ?MunicipalityContractReferenceDTO $reference,
        ): ContractReadjustmentOutputDTO {
            return new ContractReadjustmentOutputDTO(
                entryDate: $adjustment->entryDate,
                company: $adjustment->company ?? $reference?->company,
                ceirfEntryDate: $adjustment->ceirfEntryDate,
                ceirfLastMovementDate: $adjustment->ceirfLastMovementDate,
                contractNumber: $adjustment->contractNumber,
                seiProcess: $adjustment->seiProcess,
                apostilleNumber: $adjustment->apostilleNumber,
                contemplatedValue: $adjustment->contemplatedValue,
                contemplatedIncidencePeriod: $adjustment->contemplatedIncidencePeriod,
                status: $adjustment->status,
                location: $adjustment->location,
                processingTimeDays: $adjustment->processingTimeDays,
                publicationDate: $adjustment->publicationDate,
                publicationTimeDays: $adjustment->publicationTimeDays,
                observation: $adjustment->observation,
                paymentSituation: $adjustment->paymentSituation,
                paymentSei: $adjustment->paymentSei,
            );
        };

        $references = match ($input->searchType) {
            ContractSearchTypeEnum::Municipality => $this->resolver->resolve(
                new MunicipalityValueObject($input->searchTerm),
            ),
            ContractSearchTypeEnum::ContractNumber => [
                new MunicipalityContractReferenceDTO(
                    contractNumber: new ContractNumberValueObject($input->searchTerm),
                    company: null,
                ),
            ],
            ContractSearchTypeEnum::Company,
            ContractSearchTypeEnum::SeiProcess => throw new InvalidContractAdjustmentsSearchTypeException,
        };

        $groups = [];
        $total = 0;

        foreach ($references as $reference) {
            $records = array_map(
                fn (ContractReadjustmentEntity $adjustment): ContractReadjustmentOutputDTO => $toOutputDTO(
                    $adjustment,
                    $reference,
                ),
                $this->repository->findByContractNumber($reference->contractNumber),
            );

            if ($records === []) {
                continue;
            }

            $total += count($records);
            $groups[] = new ContractReadjustmentsByContractOutputDTO(
                contractNumber: $reference->contractNumber->value,
                company: $reference->company ?? $records[0]->company,
                total: count($records),
                data: $records,
            );
        }

        return new ContractAdjustmentsOutputDTO(
            searchTerm: $input->searchTerm,
            searchType: $input->searchType,
            total: $total,
            data: $groups,
        );
    }
}
