<?php

declare(strict_types=1);

namespace App\Contract\Application\Usecase;

use App\Contract\Application\DTO\ContractValueAdditivesOutputDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;
use App\Contract\Application\DTO\ValueAdditiveOutputDTO;
use App\Contract\Application\Interfaces\Usecase\FindContractValueAdditivesUsecaseInterface;
use App\Contract\Domain\Entity\ValueAdditiveEntity;
use App\Contract\Domain\Repository\ValueAdditiveRepositoryInterface;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;
use App\Contract\Enum\ContractSearchTypeEnum;
use InvalidArgumentException;

class FindContractValueAdditivesUsecase implements FindContractValueAdditivesUsecaseInterface
{
    public function __construct(
        private readonly ValueAdditiveRepositoryInterface $repository,
    ) {}

    public function __invoke(SearchContractInputDTO $input): ContractValueAdditivesOutputDTO
    {
        $valueAdditives = match ($input->searchType) {
            ContractSearchTypeEnum::Municipality => $this->repository->findByMunicipality(
                new MunicipalityValueObject($input->searchTerm),
            ),
            ContractSearchTypeEnum::ContractNumber => $this->repository->findByContractNumber(
                new ContractNumberValueObject($input->searchTerm),
            ),
            ContractSearchTypeEnum::Company,
            ContractSearchTypeEnum::SeiProcess => throw new InvalidArgumentException(
                'Value additives can only be searched by municipality or contract number.',
            ),
        };

        $data = array_map(
            static fn (ValueAdditiveEntity $valueAdditive): ValueAdditiveOutputDTO => new ValueAdditiveOutputDTO(
                entryDate: $valueAdditive->entryDate,
                stage: $valueAdditive->stage,
                contractNumber: $valueAdditive->contractNumber,
                company: $valueAdditive->company,
                municipality: $valueAdditive->municipality,
                unit: $valueAdditive->unit,
                seiProcess: $valueAdditive->seiProcess,
                type: $valueAdditive->type,
                value: $valueAdditive->value,
                status: $valueAdditive->status,
                currentLocation: $valueAdditive->currentLocation,
                processingTimeDays: $valueAdditive->processingTimeDays,
                situation: $valueAdditive->situation,
                publicationDate: $valueAdditive->publicationDate,
                publishedValue: $valueAdditive->publishedValue,
                publicationTimeDays: $valueAdditive->publicationTimeDays,
                additiveNumber: $valueAdditive->additiveNumber,
                observation: $valueAdditive->observation,
            ),
            $valueAdditives,
        );

        return new ContractValueAdditivesOutputDTO(
            searchTerm: $input->searchTerm,
            searchType: $input->searchType,
            total: count($data),
            data: $data,
        );
    }
}
