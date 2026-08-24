<?php

use App\Contract\Application\DTO\MunicipalityContractReferenceDTO;
use App\Contract\Domain\Entity\ValueAdditiveEntity;
use App\Contract\Domain\Repository\ValueAdditiveRepositoryInterface;
use App\Contract\Domain\Resolver\MunicipalityContractResolver;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;

it('returns an empty collection when a municipality has no contracts', function () {
    $repository = contractValueAddendumRepository([]);

    $references = (new MunicipalityContractResolver($repository))
        ->resolve(new MunicipalityValueObject('IBOTIRAMA'));

    expect($references)->toBe([]);
});

it('returns one contract reference and preserves its company', function () {
    $repository = contractValueAddendumRepository([
        valueAdditiveEntity('08/2023', company: '800D'),
    ]);

    $references = (new MunicipalityContractResolver($repository))
        ->resolve(new MunicipalityValueObject('IBOTIRAMA'));

    expect($references)->toHaveCount(1)
        ->and($references[0])->toBeInstanceOf(MunicipalityContractReferenceDTO::class)
        ->and($references[0]->contractNumber->value)->toBe('08/2023')
        ->and($references[0]->company)->toBe('800D');
});

it('deduplicates repeated contracts and fills a company from a later row', function () {
    $repository = contractValueAddendumRepository([
        valueAdditiveEntity('08 / 2023'),
        valueAdditiveEntity('08/2023', company: '800D'),
        valueAdditiveEntity('08/2023', company: 'Outra empresa'),
    ]);

    $references = (new MunicipalityContractResolver($repository))
        ->resolve(new MunicipalityValueObject('IBOTIRAMA'));

    expect($references)->toHaveCount(1)
        ->and($references[0]->contractNumber->value)->toBe('08/2023')
        ->and($references[0]->company)->toBe('800D');
});

it('returns every distinct contract associated with a municipality', function () {
    $repository = contractValueAddendumRepository([
        valueAdditiveEntity('08/2023', company: '800D'),
        valueAdditiveEntity('47/2025', company: 'Empresa X'),
    ]);

    $references = (new MunicipalityContractResolver($repository))
        ->resolve(new MunicipalityValueObject('FEIRA DE SANTANA'));

    expect($references)->toHaveCount(2)
        ->and(array_map(
            static fn (MunicipalityContractReferenceDTO $reference): string => $reference->contractNumber->value,
            $references,
        ))->toBe(['08/2023', '47/2025']);
});

/**
 * @param  list<ValueAdditiveEntity>  $valueAdditives
 */
function contractValueAddendumRepository(array $valueAdditives): ValueAdditiveRepositoryInterface
{
    return new class($valueAdditives) implements ValueAdditiveRepositoryInterface
    {
        /**
         * @param  list<ValueAdditiveEntity>  $valueAdditives
         */
        public function __construct(private array $valueAdditives) {}

        /**
         * @return list<ValueAdditiveEntity>
         */
        public function findByMunicipality(MunicipalityValueObject $municipality): array
        {
            return $this->valueAdditives;
        }

        /**
         * @return list<ValueAdditiveEntity>
         */
        public function findByContractNumber(ContractNumberValueObject $contractNumber): array
        {
            throw new LogicException('The resolver must not search by contract number.');
        }

        /**
         * @return list<ValueAdditiveEntity>
         */
        public function findByMunicipalityAndContractNumber(
            MunicipalityValueObject   $municipality,
            ContractNumberValueObject $contractNumber,
        ): array {
            throw new LogicException('The resolver must not combine municipality and contract searches.');
        }
    };
}

function valueAdditiveEntity(
    string $contractNumber,
    ?string $company = null,
): ValueAdditiveEntity {
    return new ValueAdditiveEntity(
        contractNumber: $contractNumber,
        municipality: 'IBOTIRAMA',
        company: $company,
        seiProcess: null,
        stage: null,
        unit: null,
        type: null,
        value: null,
        status: null,
        currentLocation: null,
        situation: null,
        publicationDate: null,
        publishedValue: null,
        additiveNumber: null,
        observation: null,
    );
}
