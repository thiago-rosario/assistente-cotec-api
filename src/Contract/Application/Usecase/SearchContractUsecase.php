<?php

declare(strict_types=1);

namespace App\Contract\Application\Usecase;

use App\Contract\Application\DTO\ContractSummaryOutputDTO;
use App\Contract\Application\DTO\MunicipalityContractReferenceDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;
use App\Contract\Application\DTO\SearchContractOutputDTO;
use App\Contract\Application\Interfaces\Resolver\MunicipalityContractResolverInterface;
use App\Contract\Application\Interfaces\Usecase\SearchContractUsecaseInterface;
use App\Contract\Domain\Entity\ContractEntity;
use App\Contract\Domain\Repository\ContractRepositoryInterface;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;
use App\Contract\Domain\ValueObject\SeiProcessValueObject;
use App\Contract\Enum\ContractSearchTypeEnum;

class SearchContractUsecase implements SearchContractUsecaseInterface
{
    public function __construct(
        private readonly ContractRepositoryInterface $repository,
        private readonly MunicipalityContractResolverInterface $resolver,
    ) {}

    public function __invoke(SearchContractInputDTO $input): SearchContractOutputDTO
    {
        if ($input->searchType === ContractSearchTypeEnum::Municipality) {
            $municipality = new MunicipalityValueObject($input->searchTerm);

            $data = array_map(
                static fn (MunicipalityContractReferenceDTO $reference): ContractSummaryOutputDTO => new ContractSummaryOutputDTO(
                    contractNumber: $reference->contractNumber->value,
                    company: $reference->company,
                    seiProcess: null,
                    municipalities: [$municipality->value],
                    municipality: $municipality->value,
                ),
                $this->resolver->resolve($municipality),
            );

            return new SearchContractOutputDTO(
                searchTerm: $input->searchTerm,
                searchType: $input->searchType,
                total: count($data),
                data: $data,
            );
        }

        $contracts = match ($input->searchType) {
            ContractSearchTypeEnum::ContractNumber => ($contract = $this->repository->findByContractNumber(
                new ContractNumberValueObject($input->searchTerm),
            )) === null ? [] : [$contract],
            ContractSearchTypeEnum::SeiProcess => ($contract = $this->repository->findBySeiProcess(
                (new SeiProcessValueObject($input->searchTerm))->value,
            )) === null ? [] : [$contract],
            ContractSearchTypeEnum::Company => $this->repository->findByCompany(trim($input->searchTerm)),
            ContractSearchTypeEnum::Municipality => [],
        };

        $data = array_map(
            static fn (ContractEntity $contract): ContractSummaryOutputDTO => new ContractSummaryOutputDTO(
                contractNumber: $contract->contractNumber,
                company: $contract->company,
                seiProcess: $contract->seiProcess,
                municipalities: $contract->municipalities,
                municipality: $contract->municipalities[0] ?? null,
            ),
            $contracts,
        );

        return new SearchContractOutputDTO(
            searchTerm: $input->searchTerm,
            searchType: $input->searchType,
            total: count($data),
            data: $data,
        );
    }
}
