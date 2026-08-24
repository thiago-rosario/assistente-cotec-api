<?php

use App\Contract\Application\DTO\ContractAdjustmentsOutputDTO;
use App\Contract\Application\DTO\ContractReadjustmentOutputDTO;
use App\Contract\Application\DTO\ContractReadjustmentsByContractOutputDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;
use App\Contract\Application\Resolver\MunicipalityContractResolver;
use App\Contract\Application\Usecase\FindContractAdjustmentsUsecase;
use App\Contract\Domain\Entity\ContractEntity;
use App\Contract\Domain\Entity\ContractReadjustmentEntity;
use App\Contract\Domain\Repository\ContractReadjustmentRepositoryInterface;
use App\Contract\Domain\Repository\ContractRepositoryInterface;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;
use App\Contract\Enum\ContractSearchTypeEnum;
use App\Contract\Exception\InvalidContractAdjustmentsSearchTypeException;

it('resolves contracts by municipality and keeps every adjustment grouped by contract', function () {
    $adjustmentRepository = adjustmentSearchRepository([
        '08/2023' => [
            adjustmentUsecaseEntity('08/2023', 'AP-1', null),
            adjustmentUsecaseEntity('08/2023', 'AP-2', 'Empresa X', '2027'),
        ],
        '47/2025' => [
            adjustmentUsecaseEntity('47/2025', 'AP-1', 'Empresa Y'),
        ],
    ]);
    $resolver = new MunicipalityContractResolver(adjustmentContractRepository([
        adjustmentContractEntity('08 / 2023', 'Empresa X'),
        adjustmentContractEntity('47/2025', 'Empresa Y'),
    ]));

    $result = (new FindContractAdjustmentsUsecase($adjustmentRepository, $resolver))(
        new SearchContractInputDTO(
            searchTerm: '  Feira   de Santana ',
            searchType: ContractSearchTypeEnum::Municipality,
        ),
    );

    expect($adjustmentRepository->searchedContractNumbers)->toBe(['08/2023', '47/2025'])
        ->and($result)->toBeInstanceOf(ContractAdjustmentsOutputDTO::class)
        ->and($result->total)->toBe(3)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(ContractReadjustmentsByContractOutputDTO::class)
        ->and($result->data[0]->contractNumber)->toBe('08/2023')
        ->and($result->data[0]->company)->toBe('Empresa X')
        ->and($result->data[0]->total)->toBe(2)
        ->and($result->data[0]->data[0])->toBeInstanceOf(ContractReadjustmentOutputDTO::class)
        ->and($result->data[0]->data[0]->company)->toBe('Empresa X')
        ->and($result->data[0]->data[0]->apostilleNumber)->toBe('AP-1')
        ->and($result->data[0]->data[1]->apostilleNumber)->toBe('AP-2')
        ->and($result->data[1]->contractNumber)->toBe('47/2025')
        ->and($result->data[1]->total)->toBe(1);
});

it('searches adjustments directly by contract number without resolving a municipality', function () {
    $adjustmentRepository = adjustmentSearchRepository([
        '08/2023' => [
            adjustmentUsecaseEntity('08/2023', 'AP-1'),
            adjustmentUsecaseEntity('08/2023', 'AP-2', 'Empresa X', '2027'),
        ],
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

    $result = (new FindContractAdjustmentsUsecase($adjustmentRepository, $resolver))(
        new SearchContractInputDTO(
            searchTerm: ' 08 / 2023 ',
            searchType: ContractSearchTypeEnum::ContractNumber,
        ),
    );

    expect($adjustmentRepository->searchedContractNumbers)->toBe(['08/2023'])
        ->and($result->total)->toBe(2)
        ->and($result->data)->toHaveCount(1)
        ->and($result->data[0]->contractNumber)->toBe('08/2023')
        ->and($result->data[0]->data[1]->apostilleNumber)->toBe('AP-2');
});

it('rejects unsupported adjustment search types', function () {
    $resolver = new MunicipalityContractResolver(adjustmentContractRepository([]));

    expect(fn () => (new FindContractAdjustmentsUsecase(
        adjustmentSearchRepository([]),
        $resolver,
    ))(new SearchContractInputDTO(
        searchTerm: 'Empresa X',
        searchType: ContractSearchTypeEnum::Company,
    )))->toThrow(
        InvalidContractAdjustmentsSearchTypeException::class,
        'Os ajustes contratuais só podem ser pesquisados por município ou número do contrato.',
    );
});

/**
 * @param  array<string, list<ContractReadjustmentEntity>>  $results
 */
function adjustmentSearchRepository(array $results): ContractReadjustmentRepositoryInterface
{
    return new class($results) implements ContractReadjustmentRepositoryInterface
    {
        /** @var list<string> */
        public array $searchedContractNumbers = [];

        /**
         * @param  array<string, list<ContractReadjustmentEntity>>  $results
         */
        public function __construct(private array $results) {}

        /** @return list<ContractReadjustmentEntity> */
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
function adjustmentContractRepository(array $contracts): ContractRepositoryInterface
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
                if (str_replace(' ', '', $contract->contractNumber) === str_replace(' ', '', $contractNumber->value)) {
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

function adjustmentContractEntity(string $contractNumber, string $company): ContractEntity
{
    return new ContractEntity(
        contractNumber: $contractNumber,
        company: $company,
        seiProcess: null,
    );
}

function adjustmentUsecaseEntity(
    string $contractNumber,
    string $apostilleNumber,
    ?string $company = 'Empresa X',
    string $incidencePeriod = '2026',
): ContractReadjustmentEntity {
    return new ContractReadjustmentEntity(
        entryDate: new DateTimeImmutable('2026-01-10'),
        company: $company,
        ceirfEntryDate: new DateTimeImmutable('2026-01-15'),
        ceirfLastMovementDate: new DateTimeImmutable('2026-01-20'),
        contractNumber: $contractNumber,
        seiProcess: '001.123/2026',
        apostilleNumber: $apostilleNumber,
        contemplatedValue: 5000.0,
        contemplatedIncidencePeriod: $incidencePeriod,
        status: 'PUBLICADO',
        location: 'CEIRF',
        processingTimeDays: 10,
        publicationDate: new DateTimeImmutable('2026-02-10'),
        publicationTimeDays: 20,
        observation: 'Reajuste anual',
        paymentSituation: 'PAGO',
        paymentSei: '001.123/2026-11',
    );
}
