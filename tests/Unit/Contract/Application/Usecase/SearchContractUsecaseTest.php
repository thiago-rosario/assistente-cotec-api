<?php

use App\Contract\Application\DTO\ContractSummaryOutputDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;
use App\Contract\Application\DTO\SearchContractOutputDTO;
use App\Contract\Application\Resolver\MunicipalityContractResolver;
use App\Contract\Application\Usecase\SearchContractUsecase;
use App\Contract\Domain\Entity\ContractEntity;
use App\Contract\Domain\Repository\ContractRepositoryInterface;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;
use App\Contract\Enum\ContractSearchTypeEnum;
use App\Contract\Exception\SeiProcessCannotBeEmptyException;

it('searches by contract number, normalizes the value object and maps the result', function () {
    $repository = searchContractRepository([
        searchContractEntity('08/2023', 'Empresa X'),
    ]);

    $result = (new SearchContractUsecase(
        $repository,
        new MunicipalityContractResolver($repository),
    ))(new SearchContractInputDTO(
        searchTerm: ' 08 / 2023 ',
        searchType: ContractSearchTypeEnum::ContractNumber,
    ));

    expect($result)->toBeInstanceOf(SearchContractOutputDTO::class)
        ->and($result->total)->toBe(1)
        ->and($result->data[0])->toBeInstanceOf(ContractSummaryOutputDTO::class)
        ->and($result->data[0]->contractNumber)->toBe('08/2023')
        ->and($result->data[0]->company)->toBe('Empresa X')
        ->and($result->data[0]->municipalities)->toBe(['Ibotirama']);
});

it('searches by SEI process and returns an empty result when no contract is found', function () {
    $repository = searchContractRepository([]);

    $result = (new SearchContractUsecase(
        $repository,
        new MunicipalityContractResolver($repository),
    ))(new SearchContractInputDTO(
        searchTerm: ' 001.123456/2023-10 ',
        searchType: ContractSearchTypeEnum::SeiProcess,
    ));

    expect($result)->toBeInstanceOf(SearchContractOutputDTO::class)
        ->and($result->total)->toBe(0)
        ->and($result->data)->toBe([]);
});

it('rejects an empty search term before consulting the repository', function (ContractSearchTypeEnum $searchType, string $exception) {
    $repository = searchContractRepository([]);

    expect(fn () => (new SearchContractUsecase(
        $repository,
        new MunicipalityContractResolver($repository),
    ))(new SearchContractInputDTO(
        searchTerm: '   ',
        searchType: $searchType,
    )))->toThrow($exception);
})->with([
    'contract number' => [ContractSearchTypeEnum::ContractNumber, InvalidArgumentException::class],
    'SEI process' => [ContractSearchTypeEnum::SeiProcess, SeiProcessCannotBeEmptyException::class],
]);

it('returns every contract associated with the contracted company', function () {
    $repository = searchContractRepository([
        searchContractEntity('08/2023', 'Empresa X'),
        searchContractEntity('47/2025', 'Empresa X'),
    ]);

    $result = (new SearchContractUsecase(
        $repository,
        new MunicipalityContractResolver($repository),
    ))(new SearchContractInputDTO(
        searchTerm: '  Empresa X  ',
        searchType: ContractSearchTypeEnum::Company,
    ));

    expect($result->total)->toBe(2)
        ->and(array_map(
            static fn (ContractSummaryOutputDTO $summary): string => $summary->contractNumber,
            $result->data,
        ))->toBe(['08/2023', '47/2025']);
});

it('uses the official contract register to find every municipality contract', function () {
    $repository = searchContractRepository([
        searchContractEntity('08/2023', 'Empresa A'),
        searchContractEntity('47/2025', 'Empresa B'),
    ]);

    $result = (new SearchContractUsecase(
        $repository,
        new MunicipalityContractResolver($repository),
    ))(new SearchContractInputDTO(
        searchTerm: ' Feira  de  Santana ',
        searchType: ContractSearchTypeEnum::Municipality,
    ));

    expect($result->total)->toBe(2)
        ->and($result->data[0]->contractNumber)->toBe('08/2023')
        ->and($result->data[0]->company)->toBe('Empresa A')
        ->and($result->data[0]->municipality)->toBe('Feira de Santana')
        ->and($result->data[1]->contractNumber)->toBe('47/2025');
});

/**
 * @param  list<ContractEntity>  $contracts
 */
function searchContractRepository(array $contracts): ContractRepositoryInterface
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
            return $this->contracts;
        }

        /** @return list<ContractEntity> */
        public function findByCompany(string $company): array
        {
            return array_values(array_filter(
                $this->contracts,
                static fn (ContractEntity $contract): bool => $contract->company === $company,
            ));
        }
    };
}

function searchContractEntity(string $contractNumber, string $company): ContractEntity
{
    return new ContractEntity(
        contractNumber: $contractNumber,
        company: $company,
        seiProcess: '001.123456/2023-10',
        municipalities: ['Ibotirama'],
    );
}
