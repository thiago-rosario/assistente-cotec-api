<?php

declare(strict_types=1);

namespace App\Contract\Application\Usecase;

use App\Contract\Application\DTO\FindContractSummaryOutputDTO;
use App\Contract\Application\DTO\MunicipalityContractReferenceDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;
use App\Contract\Application\Interfaces\Assembly\ContractSummaryAssemblerInterface;
use App\Contract\Application\Interfaces\Resolver\MunicipalityContractResolverInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractAdjustmentsUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractExecutionDeadlineUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractSummaryUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractValueAdditivesUsecaseInterface;
use App\Contract\Application\Trait\LocatesContractSummaryRecordsTrait;
use App\Contract\Domain\Repository\ContractRepositoryInterface;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;
use App\Contract\Enum\ContractSearchTypeEnum;
use InvalidArgumentException;

class FindContractSummaryUsecase implements FindContractSummaryUsecaseInterface
{
    use LocatesContractSummaryRecordsTrait;

    public function __construct(
        private readonly ContractRepositoryInterface $repository,
        private readonly MunicipalityContractResolverInterface $resolver,
        private readonly FindContractValueAdditivesUsecaseInterface $valueAdditivesUsecase,
        private readonly FindContractAdjustmentsUsecaseInterface $adjustmentsUsecase,
        private readonly FindContractExecutionDeadlineUsecaseInterface $executionDeadlineUsecase,
        private readonly ContractSummaryAssemblerInterface $assembler,
    ) {}

    public function __invoke(SearchContractInputDTO $input): FindContractSummaryOutputDTO
    {
        $municipality = null;

        $references = match ($input->searchType) {
            ContractSearchTypeEnum::Municipality => (function () use ($input, &$municipality): array {
                $municipality = new MunicipalityValueObject($input->searchTerm);

                return $this->uniqueReferences($this->resolver->resolve($municipality));
            })(),
            ContractSearchTypeEnum::ContractNumber => [new MunicipalityContractReferenceDTO(
                contractNumber: new ContractNumberValueObject($input->searchTerm),
                company: null,
            )],
            ContractSearchTypeEnum::Company,
            ContractSearchTypeEnum::SeiProcess => throw new InvalidArgumentException(
                'Contract summaries can only be searched by municipality or contract number.',
            ),
        };

        $summaries = [];

        foreach ($references as $reference) {
            $contract = $this->findContract($reference->contractNumber);

            if ($contract === null) {
                continue;
            }

            $contractInput = new SearchContractInputDTO(
                searchTerm: $contract->contractNumber,
                searchType: ContractSearchTypeEnum::ContractNumber,
            );
            $valueAdditives = ($this->valueAdditivesUsecase)($contractInput)->data;
            $adjustments = ($this->adjustmentsUsecase)($contractInput)->data;
            $executionDeadlines = ($this->executionDeadlineUsecase)($contractInput)->data;

            $summaries[] = $this->assembler->assemble(
                contract: $contract,
                reference: $reference,
                municipality: $municipality?->value,
                valueAdditives: $valueAdditives,
                readjustments: array_merge(
                    ...array_map(
                        static fn (object $group): array => $group->data,
                        $adjustments,
                    ),
                ),
                executionDeadlines: $executionDeadlines,
            );
        }

        return new FindContractSummaryOutputDTO(
            searchTerm: $input->searchTerm,
            searchType: $input->searchType,
            total: count($summaries),
            data: $summaries,
        );
    }
}
