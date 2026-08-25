<?php

use App\Contract\Application\DTO\ContractValueAdditivesOutputDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;
use App\Contract\Application\DTO\ValueAdditiveOutputDTO;
use App\Contract\Application\Usecase\FindContractValueAdditivesUsecase;
use App\Contract\Domain\Entity\ValueAdditiveEntity;
use App\Contract\Domain\Repository\ValueAdditiveRepositoryInterface;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;
use App\Contract\Enum\ContractSearchTypeEnum;

it('finds value additives by municipality and maps every source field', function () {
    $repository = additiveSearchRepository([
        'municipality' => [valueAdditiveUsecaseEntity()],
    ]);

    $result = (new FindContractValueAdditivesUsecase($repository))(new SearchContractInputDTO(
        searchTerm: '  Feira   de Santana ',
        searchType: ContractSearchTypeEnum::Municipality,
    ));

    expect($repository->calledMethod)->toBe('findByMunicipality')
        ->and($repository->searchValue)->toBeInstanceOf(MunicipalityValueObject::class)
        ->and($repository->searchValue->value)->toBe('Feira de Santana')
        ->and($result)->toBeInstanceOf(ContractValueAdditivesOutputDTO::class)
        ->and($result->total)->toBe(1)
        ->and($result->data[0])->toBeInstanceOf(ValueAdditiveOutputDTO::class)
        ->and($result->data[0]->entryDate)->toBe('2026-01-10')
        ->and($result->data[0]->stage)->toBe('Publicação')
        ->and($result->data[0]->contractNumber)->toBe('08/2023')
        ->and($result->data[0]->company)->toBe('Empresa X')
        ->and($result->data[0]->municipality)->toBe('Feira de Santana')
        ->and($result->data[0]->unit)->toBe('Unidade A')
        ->and($result->data[0]->seiProcess)->toBe('001.123/2026')
        ->and($result->data[0]->type)->toBe('SUPRESSÃO')
        ->and($result->data[0]->value)->toBe(-10000.0)
        ->and($result->data[0]->status)->toBe('PUBLICADO')
        ->and($result->data[0]->currentLocation)->toBe('CEIRF')
        ->and($result->data[0]->processingTimeDays)->toBe(12)
        ->and($result->data[0]->situation)->toBe('PAGO')
        ->and($result->data[0]->publicationDate)->toBe('2026-02-10')
        ->and($result->data[0]->publishedValue)->toBe(90000.0)
        ->and($result->data[0]->publicationTimeDays)->toBe(7)
        ->and($result->data[0]->additiveNumber)->toBe('2')
        ->and($result->data[0]->observation)->toBe('Ajuste contratual');
});

it('finds value additives directly by contract number', function () {
    $repository = additiveSearchRepository([
        'contractNumber' => [valueAdditiveUsecaseEntity()],
    ]);

    $result = (new FindContractValueAdditivesUsecase($repository))(new SearchContractInputDTO(
        searchTerm: ' 08 / 2023 ',
        searchType: ContractSearchTypeEnum::ContractNumber,
    ));

    expect($repository->calledMethod)->toBe('findByContractNumber')
        ->and($repository->searchValue)->toBeInstanceOf(ContractNumberValueObject::class)
        ->and($repository->searchValue->value)->toBe('08/2023')
        ->and($result->total)->toBe(1);
});

it('rejects company and SEI process as value additive search criteria', function (ContractSearchTypeEnum $searchType) {
    expect(fn () => (new FindContractValueAdditivesUsecase(additiveSearchRepository([])))(
        new SearchContractInputDTO(
            searchTerm: 'Empresa X',
            searchType: $searchType,
        ),
    ))->toThrow(InvalidArgumentException::class);
})->with([
    'company' => ContractSearchTypeEnum::Company,
    'SEI process' => ContractSearchTypeEnum::SeiProcess,
]);

/**
 * @param  array<string, list<ValueAdditiveEntity>>  $results
 */
function additiveSearchRepository(array $results): ValueAdditiveRepositoryInterface
{
    return new class($results) implements ValueAdditiveRepositoryInterface
    {
        public string $calledMethod = '';

        public mixed $searchValue = null;

        /**
         * @param  array<string, list<ValueAdditiveEntity>>  $results
         */
        public function __construct(private array $results) {}

        /**
         * @return list<ValueAdditiveEntity>
         */
        public function findByMunicipality(MunicipalityValueObject $municipality): array
        {
            $this->calledMethod = __FUNCTION__;
            $this->searchValue = $municipality;

            return $this->results['municipality'] ?? [];
        }

        /**
         * @return list<ValueAdditiveEntity>
         */
        public function findByContractNumber(ContractNumberValueObject $contractNumber): array
        {
            $this->calledMethod = __FUNCTION__;
            $this->searchValue = $contractNumber;

            return $this->results['contractNumber'] ?? [];
        }

        /**
         * @return list<ValueAdditiveEntity>
         */
        public function findByMunicipalityAndContractNumber(
            MunicipalityValueObject $municipality,
            ContractNumberValueObject $contractNumber,
        ): array {
            return [];
        }
    };
}

function valueAdditiveUsecaseEntity(): ValueAdditiveEntity
{
    return new ValueAdditiveEntity(
        contractNumber: '08/2023',
        municipality: 'Feira de Santana',
        company: 'Empresa X',
        seiProcess: '001.123/2026',
        stage: 'Publicação',
        unit: 'Unidade A',
        type: 'SUPRESSÃO',
        value: -10000.0,
        status: 'PUBLICADO',
        currentLocation: 'CEIRF',
        situation: 'PAGO',
        publicationDate: '2026-02-10',
        publishedValue: 90000.0,
        additiveNumber: '2',
        observation: 'Ajuste contratual',
        entryDate: '2026-01-10',
        processingTimeDays: 12,
        publicationTimeDays: 7,
    );
}
