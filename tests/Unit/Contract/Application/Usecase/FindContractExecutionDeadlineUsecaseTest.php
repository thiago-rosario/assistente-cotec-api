<?php

use App\Contract\Application\DTO\ContractExecutionDeadlineOutputDTO;
use App\Contract\Application\DTO\ContractExecutionDeadlinesOutputDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;
use App\Contract\Application\Resolver\MunicipalityContractResolver;
use App\Contract\Application\Service\ContractRemainingDaysCalculatorService;
use App\Contract\Application\Usecase\FindContractExecutionDeadlineUsecase;
use App\Contract\Domain\Entity\ContractEntity;
use App\Contract\Domain\Entity\ContractExecutionDeadlineEntity;
use App\Contract\Domain\Repository\ContractExecutionDeadlineRepositoryInterface;
use App\Contract\Domain\Repository\ContractRepositoryInterface;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;
use App\Contract\Enum\ContractSearchTypeEnum;

it('resolves contracts by municipality and preserves all deadline fields', function () {
    $contractRepository = executionDeadlineContractRepository([
        executionDeadlineContract('08/2023', 'Empresa A'),
        executionDeadlineContract('47/2025', 'Empresa B'),
    ]);
    $repository = executionDeadlineSearchRepository([
        '08/2023' => [executionDeadlineRecord(
            contractNumber: '08/2023',
            company: null,
            municipality: 'JITAÚNA - DT',
            unit: null,
            executionEndDate: '2026-08-31',
            validityEndDate: '2026-09-23',
        )],
        '47/2025' => [executionDeadlineRecord(
            contractNumber: '47/2025',
            company: 'Empresa B',
            municipality: null,
            unit: 'Unidade B',
            executionEndDate: '2026-08-23',
            validityEndDate: null,
        )],
    ]);

    $result = (new FindContractExecutionDeadlineUsecase(
        repository: $repository,
        resolver: new MunicipalityContractResolver($contractRepository),
        remainingDaysCalculator: new ContractRemainingDaysCalculatorService,
        referenceDate: new DateTimeImmutable('2026-08-24'),
    ))(new SearchContractInputDTO(
        searchTerm: '  Jitaúna  ',
        searchType: ContractSearchTypeEnum::Municipality,
    ));

    expect($repository->searchedContractNumbers)->toBe(['08/2023', '47/2025'])
        ->and($result)->toBeInstanceOf(ContractExecutionDeadlinesOutputDTO::class)
        ->and($result->total)->toBe(2)
        ->and($result->data[0])->toBeInstanceOf(ContractExecutionDeadlineOutputDTO::class)
        ->and($result->data[0]->entryDate)->toEqual(new DateTimeImmutable('2026-01-10'))
        ->and($result->data[0]->company)->toBe('Empresa A')
        ->and($result->data[0]->contractNumber)->toBe('08/2023')
        ->and($result->data[0]->validityEndDate)->toEqual(new DateTimeImmutable('2026-09-23'))
        ->and($result->data[0]->municipality)->toBe('JITAÚNA - DT')
        ->and($result->data[0]->unit)->toBeNull()
        ->and($result->data[0]->executionEndDate)->toEqual(new DateTimeImmutable('2026-08-31'))
        ->and($result->data[0]->remainingExecutionDays)->toBe(7)
        ->and($result->data[0]->remainingValidityDays)->toBe(30)
        ->and($result->data[0]->contractSituation)->toBe('EM EXECUÇÃO')
        ->and($result->data[0]->seiProcess)->toBe('001.123/2026')
        ->and($result->data[0]->location)->toBe('CEIRF')
        ->and($result->data[0]->deadlineAddendumStatus)->toBe('EM ANÁLISE')
        ->and($result->data[0]->processingTimeDays)->toBe(12)
        ->and($result->data[0]->publicationDate)->toEqual(new DateTimeImmutable('2026-02-10'))
        ->and($result->data[0]->publicationTimeDays)->toBe(20)
        ->and($result->data[0]->observation)->toBe('Prazo prorrogado')
        ->and($result->data[1]->unit)->toBe('Unidade B')
        ->and($result->data[1]->remainingExecutionDays)->toBe(-1)
        ->and($result->data[1]->remainingValidityDays)->toBeNull();
});

it('searches directly by contract number without consulting the municipality resolver', function () {
    $repository = executionDeadlineSearchRepository([
        '08/2023' => [executionDeadlineRecord(
            contractNumber: '08/2023',
            company: 'Empresa X',
            municipality: null,
            unit: 'Unidade X',
            executionEndDate: null,
            validityEndDate: null,
        )],
    ]);
    $resolver = new MunicipalityContractResolver(new class implements ContractRepositoryInterface
    {
        public function findByContractNumber(ContractNumberValueObject $contractNumber): ?ContractEntity
        {
            throw new LogicException('The resolver must not be consulted for contract searches.');
        }

        public function findBySeiProcess(string $seiProcess): ?ContractEntity
        {
            throw new LogicException('The resolver must not be consulted for contract searches.');
        }

        public function findByMunicipality(MunicipalityValueObject $municipality): array
        {
            throw new LogicException('The resolver must not be consulted for contract searches.');
        }

        public function findByCompany(string $company): array
        {
            throw new LogicException('The resolver must not be consulted for contract searches.');
        }
    });

    $result = (new FindContractExecutionDeadlineUsecase(
        repository: $repository,
        resolver: $resolver,
        remainingDaysCalculator: new ContractRemainingDaysCalculatorService,
        referenceDate: new DateTimeImmutable('2026-08-24'),
    ))(new SearchContractInputDTO(
        searchTerm: ' 08 / 2023 ',
        searchType: ContractSearchTypeEnum::ContractNumber,
    ));

    expect($repository->searchedContractNumbers)->toBe(['08/2023'])
        ->and($result->total)->toBe(1)
        ->and($result->data[0]->contractNumber)->toBe('08/2023')
        ->and($result->data[0]->unit)->toBe('Unidade X')
        ->and($result->data[0]->remainingExecutionDays)->toBeNull()
        ->and($result->data[0]->remainingValidityDays)->toBeNull();
});

it('rejects search types that are not municipality or contract number', function () {
    expect(fn () => (new FindContractExecutionDeadlineUsecase(
        repository: executionDeadlineSearchRepository([]),
        resolver: new MunicipalityContractResolver(executionDeadlineContractRepository([])),
        remainingDaysCalculator: new ContractRemainingDaysCalculatorService,
        referenceDate: new DateTimeImmutable('2026-08-24'),
    ))(new SearchContractInputDTO(
        searchTerm: 'Empresa X',
        searchType: ContractSearchTypeEnum::Company,
    )))->toThrow(
        InvalidArgumentException::class,
        'Execution deadlines can only be searched by municipality or contract number.',
    );
});

/**
 * @param  array<string, list<ContractExecutionDeadlineEntity>>  $results
 */
function executionDeadlineSearchRepository(array $results): ContractExecutionDeadlineRepositoryInterface
{
    return new class($results) implements ContractExecutionDeadlineRepositoryInterface
    {
        /** @var list<string> */
        public array $searchedContractNumbers = [];

        /**
         * @param  array<string, list<ContractExecutionDeadlineEntity>>  $results
         */
        public function __construct(private array $results) {}

        /**
         * @return list<ContractExecutionDeadlineEntity>
         */
        public function findByContractNumber(ContractNumberValueObject $contractNumber): array
        {
            $this->searchedContractNumbers[] = $contractNumber->value;

            return $this->results[$contractNumber->value] ?? [];
        }
    };
}

/**
 * @param  list<ContractEntity>  $contracts
 */
function executionDeadlineContractRepository(array $contracts): ContractRepositoryInterface
{
    return new class($contracts) implements ContractRepositoryInterface
    {
        /**
         * @param  list<ContractEntity>  $contracts
         */
        public function __construct(private array $contracts) {}

        public function findByContractNumber(ContractNumberValueObject $contractNumber): ?ContractEntity
        {
            foreach ($this->contracts as $contract) {
                if ($contract->contractNumber === $contractNumber->value) {
                    return $contract;
                }
            }

            return null;
        }

        public function findBySeiProcess(string $seiProcess): ?ContractEntity
        {
            return null;
        }

        /** @return list<ContractEntity> */
        public function findByMunicipality(MunicipalityValueObject $municipality): array
        {
            return $this->contracts;
        }

        /** @return list<ContractEntity> */
        public function findByCompany(string $company): array
        {
            return [];
        }
    };
}

function executionDeadlineContract(string $contractNumber, string $company): ContractEntity
{
    return new ContractEntity(
        contractNumber: $contractNumber,
        company: $company,
        seiProcess: '001.123/2026',
    );
}

function executionDeadlineRecord(
    string $contractNumber,
    ?string $company,
    ?string $municipality,
    ?string $unit,
    ?string $executionEndDate,
    ?string $validityEndDate,
): ContractExecutionDeadlineEntity {
    return new ContractExecutionDeadlineEntity(
        contractNumber: $contractNumber,
        company: $company,
        municipality: $municipality,
        seiProcess: '001.123/2026',
        validityEndDate: $validityEndDate === null ? null : new DateTimeImmutable($validityEndDate),
        executionEndDate: $executionEndDate === null ? null : new DateTimeImmutable($executionEndDate),
        contractSituation: 'EM EXECUÇÃO',
        deadlineAddendumStatus: 'EM ANÁLISE',
        location: 'CEIRF',
        publicationDate: new DateTimeImmutable('2026-02-10'),
        observation: 'Prazo prorrogado',
        entryDate: new DateTimeImmutable('2026-01-10'),
        processingTimeDays: 12,
        publicationTimeDays: 20,
        unit: $unit,
    );
}
