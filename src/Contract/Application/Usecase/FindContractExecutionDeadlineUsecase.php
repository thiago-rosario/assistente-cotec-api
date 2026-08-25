<?php

declare(strict_types=1);

namespace App\Contract\Application\Usecase;

use App\Contract\Application\DTO\ContractExecutionDeadlineOutputDTO;
use App\Contract\Application\DTO\ContractExecutionDeadlinesOutputDTO;
use App\Contract\Application\DTO\MunicipalityContractReferenceDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;
use App\Contract\Application\Interfaces\Resolver\MunicipalityContractResolverInterface;
use App\Contract\Application\Interfaces\Service\ContractRemainingDaysCalculatorServiceInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractExecutionDeadlineUsecaseInterface;
use App\Contract\Domain\Entity\ContractExecutionDeadlineEntity;
use App\Contract\Domain\Repository\ContractExecutionDeadlineRepositoryInterface;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;
use App\Contract\Enum\ContractSearchTypeEnum;
use DateTimeImmutable;
use InvalidArgumentException;

class FindContractExecutionDeadlineUsecase implements FindContractExecutionDeadlineUsecaseInterface
{
    public function __construct(
        private readonly ContractExecutionDeadlineRepositoryInterface $repository,
        private readonly MunicipalityContractResolverInterface $resolver,
        private readonly ContractRemainingDaysCalculatorServiceInterface $remainingDaysCalculator,
        private readonly DateTimeImmutable $referenceDate,
    ) {}

    public function __invoke(SearchContractInputDTO $input): ContractExecutionDeadlinesOutputDTO
    {
        $toOutputDTO = function (
            ContractExecutionDeadlineEntity $deadline,
            ?MunicipalityContractReferenceDTO $reference,
        ): ContractExecutionDeadlineOutputDTO {
            return new ContractExecutionDeadlineOutputDTO(
                entryDate: $deadline->entryDate,
                company: $deadline->company ?? $reference?->company,
                contractNumber: $deadline->contractNumber,
                validityEndDate: $deadline->validityEndDate,
                municipality: $deadline->municipality,
                unit: $deadline->unit,
                executionEndDate: $deadline->executionEndDate,
                remainingExecutionDays: $this->remainingDaysCalculator->calculate(
                    $deadline->executionEndDate,
                    $this->referenceDate,
                ),
                remainingValidityDays: $this->remainingDaysCalculator->calculate(
                    $deadline->validityEndDate,
                    $this->referenceDate,
                ),
                contractSituation: $deadline->contractSituation,
                seiProcess: $deadline->seiProcess,
                location: $deadline->location,
                deadlineAddendumStatus: $deadline->deadlineAddendumStatus,
                processingTimeDays: $deadline->processingTimeDays,
                publicationDate: $deadline->publicationDate,
                publicationTimeDays: $deadline->publicationTimeDays,
                observation: $deadline->observation,
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
            ContractSearchTypeEnum::SeiProcess => throw new InvalidArgumentException(
                'Execution deadlines can only be searched by municipality or contract number.',
            ),
        };

        $data = [];

        foreach ($references as $reference) {
            foreach ($this->repository->findByContractNumber($reference->contractNumber) as $deadline) {
                $data[] = $toOutputDTO($deadline, $reference);
            }
        }

        return new ContractExecutionDeadlinesOutputDTO(
            searchTerm: $input->searchTerm,
            searchType: $input->searchType,
            total: count($data),
            data: $data,
        );
    }
}
