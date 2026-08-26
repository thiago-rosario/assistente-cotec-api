<?php

use App\Contract\Application\Assembly\ContractSummaryAssembler;
use App\Contract\Application\DTO\ContractExecutionDeadlineOutputDTO;
use App\Contract\Application\DTO\ContractReadjustmentOutputDTO;
use App\Contract\Application\DTO\ContractSummaryOutputDTO;
use App\Contract\Application\DTO\FindContractSummaryOutputDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;
use App\Contract\Application\Resolver\MunicipalityContractResolver;
use App\Contract\Application\Service\ContractRemainingDaysCalculatorService;
use App\Contract\Application\Usecase\FindContractAdjustmentsUsecase;
use App\Contract\Application\Usecase\FindContractExecutionDeadlineUsecase;
use App\Contract\Application\Usecase\FindContractSummaryUsecase;
use App\Contract\Application\Usecase\FindContractValueAdditivesUsecase;
use App\Contract\Domain\Entity\ContractEntity;
use App\Contract\Domain\Entity\ContractExecutionDeadlineEntity;
use App\Contract\Domain\Entity\ContractReadjustmentEntity;
use App\Contract\Domain\Entity\ValueAdditiveEntity;
use App\Contract\Domain\Repository\ContractExecutionDeadlineRepositoryInterface;
use App\Contract\Domain\Repository\ContractReadjustmentRepositoryInterface;
use App\Contract\Domain\Repository\ContractRepositoryInterface;
use App\Contract\Domain\Repository\ValueAdditiveRepositoryInterface;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;
use App\Contract\Enum\ContractSearchTypeEnum;

it('builds a complete municipality summary for every contract without mixing records', function () {
    $contracts = [
        summaryContractEntity('08/2023', 'Empresa A'),
        summaryContractEntity('47/2025', 'Empresa B'),
    ];
    $contractRepository = summaryContractRepository($contracts);
    $resolver = new MunicipalityContractResolver($contractRepository);
    $usecase = summaryUsecase(
        contractRepository: $contractRepository,
        resolver: $resolver,
        valueAdditives: [
            '08/2023' => [summaryValueAdditive('08/2023', 'Empresa A'), summaryValueAdditive('08/2023', 'Empresa A')],
        ],
        adjustments: [
            '08/2023' => [summaryReadjustment('08/2023', 'Empresa A')],
            '47/2025' => [summaryReadjustment('47/2025', 'Empresa B')],
        ],
        deadlines: [
            '08/2023' => [summaryDeadline('08/2023', 'Empresa A'), summaryDeadline('08/2023', 'Empresa A')],
            '47/2025' => [summaryDeadline('47/2025', 'Empresa B')],
        ],
    );

    $result = $usecase(new SearchContractInputDTO(
        searchTerm: '  Feira   de Santana ',
        searchType: ContractSearchTypeEnum::Municipality,
    ));

    expect($result)->toBeInstanceOf(FindContractSummaryOutputDTO::class)
        ->and($result->searchTerm)->toBe('  Feira   de Santana ')
        ->and($result->searchType)->toBe(ContractSearchTypeEnum::Municipality)
        ->and($result->total)->toBe(2)
        ->and($result->data[0])->toBeInstanceOf(ContractSummaryOutputDTO::class)
        ->and($result->data[0]->contractNumber)->toBe('08/2023')
        ->and($result->data[0]->company)->toBe('Empresa A')
        ->and($result->data[0]->municipality)->toBe('Feira de Santana')
        ->and($result->data[0]->valueAdditives)->toHaveCount(2)
        ->and($result->data[0]->readjustments)->toHaveCount(1)
        ->and($result->data[0]->executionDeadlines)->toHaveCount(2)
        ->and($result->data[0]->processes)->toContain('001.123456/2026-10')
        ->and($result->data[0]->statuses)->toContain('PUBLICADO')
        ->and($result->data[0]->observations)->toContain('Prazo prorrogado')
        ->and($result->data[1]->contractNumber)->toBe('47/2025')
        ->and($result->data[1]->valueAdditives)->toBe([])
        ->and($result->data[1]->readjustments[0])->toBeInstanceOf(ContractReadjustmentOutputDTO::class)
        ->and($result->data[1]->executionDeadlines[0])->toBeInstanceOf(ContractExecutionDeadlineOutputDTO::class);
});

it('builds the same complete summary directly by contract number', function () {
    $contract = summaryContractEntity('08/2023', 'Empresa A');
    $contractRepository = summaryContractRepository([$contract], failMunicipalitySearch: true);
    $usecase = summaryUsecase(
        contractRepository: $contractRepository,
        resolver: new MunicipalityContractResolver($contractRepository),
        valueAdditives: ['08/2023' => [summaryValueAdditive('08/2023', 'Empresa A')]],
        adjustments: ['08/2023' => [summaryReadjustment('08/2023', 'Empresa A')]],
        deadlines: ['08/2023' => [summaryDeadline('08/2023', 'Empresa A')]],
    );

    $result = $usecase(new SearchContractInputDTO(
        searchTerm: ' 08 / 2023 ',
        searchType: ContractSearchTypeEnum::ContractNumber,
    ));

    expect($result->total)->toBe(1)
        ->and($result->data[0]->contractNumber)->toBe('08/2023')
        ->and($result->data[0]->valueAdditives)->toHaveCount(1)
        ->and($result->data[0]->readjustments)->toHaveCount(1)
        ->and($result->data[0]->executionDeadlines)->toHaveCount(1);
});

it('returns an empty summary when the official register has no municipality contracts', function () {
    $contractRepository = summaryContractRepository([]);
    $usecase = summaryUsecase(
        contractRepository: $contractRepository,
        resolver: new MunicipalityContractResolver($contractRepository),
        valueAdditives: [],
        adjustments: [],
        deadlines: [],
    );

    $result = $usecase(new SearchContractInputDTO(
        searchTerm: 'Ibotirama',
        searchType: ContractSearchTypeEnum::Municipality,
    ));

    expect($result->total)->toBe(0)
        ->and($result->data)->toBe([]);
});

/**
 * @param  list<ContractEntity>  $contracts
 * @param  array<string, list<ValueAdditiveEntity>>  $valueAdditives
 * @param  array<string, list<ContractReadjustmentEntity>>  $adjustments
 * @param  array<string, list<ContractExecutionDeadlineEntity>>  $deadlines
 */
function summaryUsecase(
    ContractRepositoryInterface $contractRepository,
    MunicipalityContractResolver $resolver,
    array $valueAdditives,
    array $adjustments,
    array $deadlines,
): FindContractSummaryUsecase {
    $adjustmentUsecase = new FindContractAdjustmentsUsecase(
        summaryAdjustmentRepository($adjustments),
        $resolver,
    );
    $deadlineUsecase = new FindContractExecutionDeadlineUsecase(
        summaryDeadlineRepository($deadlines),
        $resolver,
        new ContractRemainingDaysCalculatorService,
        new DateTimeImmutable('2026-08-24'),
    );

    return new FindContractSummaryUsecase(
        valueAdditivesUsecase: new FindContractValueAdditivesUsecase(
            summaryValueAdditiveRepository($valueAdditives),
        ),
        adjustmentsUsecase: $adjustmentUsecase,
        executionDeadlineUsecase: $deadlineUsecase,
        assembler: new ContractSummaryAssembler,
    );
}

/**
 * @param  list<ContractEntity>  $contracts
 */
function summaryContractRepository(array $contracts, bool $failMunicipalitySearch = false): ContractRepositoryInterface
{
    return new class($contracts, $failMunicipalitySearch) implements ContractRepositoryInterface
    {
        /**
         * @param  list<ContractEntity>  $contracts
         */
        public function __construct(
            private array $contracts,
            private bool $failMunicipalitySearch,
        ) {}

        public function findByContractNumber(ContractNumberValueObject $contractNumber): ?ContractEntity
        {
            foreach ($this->contracts as $contract) {
                if (str_replace(' ', '', $contract->contractNumber) === $contractNumber->value) {
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
            if ($this->failMunicipalitySearch) {
                throw new LogicException('Municipality search must not be consulted.');
            }

            return $this->contracts;
        }

        /** @return list<ContractEntity> */
        public function findByCompany(string $company): array
        {
            return [];
        }
    };
}

/**
 * @param  array<string, list<ValueAdditiveEntity>>  $results
 */
function summaryValueAdditiveRepository(array $results): ValueAdditiveRepositoryInterface
{
    return new class($results) implements ValueAdditiveRepositoryInterface
    {
        /**
         * @param  array<string, list<ValueAdditiveEntity>>  $results
         */
        public function __construct(private array $results) {}

        /** @return list<ValueAdditiveEntity> */
        public function findByMunicipality(MunicipalityValueObject $municipality): array
        {
            $records = [];

            foreach ($this->results as $valueAdditives) {
                foreach ($valueAdditives as $valueAdditive) {
                    if (mb_strtoupper($valueAdditive->municipality) === $municipality->normalized) {
                        $records[] = $valueAdditive;
                    }
                }
            }

            return $records;
        }

        /** @return list<ValueAdditiveEntity> */
        public function findByContractNumber(ContractNumberValueObject $contractNumber): array
        {
            return $this->results[$contractNumber->value] ?? [];
        }

        /** @return list<ValueAdditiveEntity> */
        public function findByMunicipalityAndContractNumber(
            MunicipalityValueObject $municipality,
            ContractNumberValueObject $contractNumber,
        ): array {
            return [];
        }
    };
}

/**
 * @param  array<string, list<ContractReadjustmentEntity>>  $results
 */
function summaryAdjustmentRepository(array $results): ContractReadjustmentRepositoryInterface
{
    return new class($results) implements ContractReadjustmentRepositoryInterface
    {
        /**
         * @param  array<string, list<ContractReadjustmentEntity>>  $results
         */
        public function __construct(private array $results) {}

        /** @return list<ContractReadjustmentEntity> */
        public function findByContractNumber(ContractNumberValueObject $contractNumber): array
        {
            return $this->results[$contractNumber->value] ?? [];
        }
    };
}

/**
 * @param  array<string, list<ContractExecutionDeadlineEntity>>  $results
 */
function summaryDeadlineRepository(array $results): ContractExecutionDeadlineRepositoryInterface
{
    return new class($results) implements ContractExecutionDeadlineRepositoryInterface
    {
        /**
         * @param  array<string, list<ContractExecutionDeadlineEntity>>  $results
         */
        public function __construct(private array $results) {}

        /** @return list<ContractExecutionDeadlineEntity> */
        public function findByContractNumber(ContractNumberValueObject $contractNumber): array
        {
            return $this->results[$contractNumber->value] ?? [];
        }
    };
}

function summaryContractEntity(string $contractNumber, ?string $company): ContractEntity
{
    return new ContractEntity(
        contractNumber: $contractNumber,
        company: $company,
        seiProcess: '001.123456/2026-10',
        municipalities: ['Feira de Santana'],
        object: 'Construção de unidade pública',
        initialValue: 100000.0,
        updatedValue: 125000.0,
        validityStartDate: new DateTimeImmutable('2026-01-01'),
    );
}

function summaryValueAdditive(string $contractNumber, ?string $company): ValueAdditiveEntity
{
    return new ValueAdditiveEntity(
        contractNumber: $contractNumber,
        municipality: 'Feira de Santana',
        company: $company,
        seiProcess: '001.123456/2026-10',
        stage: null,
        unit: null,
        type: 'ACRÉSCIMO',
        value: 12500.0,
        status: 'PUBLICADO',
        currentLocation: null,
        situation: null,
        publicationDate: null,
        publishedValue: 12500.0,
        additiveNumber: '1',
        observation: 'Aditivo de valor',
    );
}

function summaryReadjustment(string $contractNumber, ?string $company): ContractReadjustmentEntity
{
    return new ContractReadjustmentEntity(
        entryDate: new DateTimeImmutable('2026-02-01'),
        company: $company,
        ceirfEntryDate: null,
        ceirfLastMovementDate: null,
        contractNumber: $contractNumber,
        seiProcess: '001.123456/2026-10',
        apostilleNumber: 'AP-1',
        contemplatedValue: 5000.0,
        contemplatedIncidencePeriod: '2026',
        status: 'PUBLICADO',
        location: null,
        processingTimeDays: 10,
        publicationDate: new DateTimeImmutable('2026-03-01'),
        publicationTimeDays: 20,
        observation: 'Reequilíbrio econômico-financeiro',
        paymentSituation: 'PAGO',
        paymentSei: '001.123456/2026-11',
    );
}

function summaryDeadline(string $contractNumber, ?string $company): ContractExecutionDeadlineEntity
{
    return new ContractExecutionDeadlineEntity(
        contractNumber: $contractNumber,
        company: $company,
        municipality: 'Feira de Santana',
        seiProcess: '001.123456/2026-10',
        validityEndDate: new DateTimeImmutable('2027-01-01'),
        executionEndDate: new DateTimeImmutable('2026-12-01'),
        contractSituation: 'EM EXECUÇÃO',
        deadlineAddendumStatus: null,
        location: null,
        publicationDate: null,
        observation: 'Prazo prorrogado',
    );
}
