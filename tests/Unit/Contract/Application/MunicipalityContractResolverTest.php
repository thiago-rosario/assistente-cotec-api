<?php

use App\Contract\Application\DTO\MunicipalityContractReferenceDTO;
use App\Contract\Application\Resolver\MunicipalityContractResolver;
use App\Contract\Domain\Entity\ContractEntity;
use App\Contract\Domain\Repository\ContractRepositoryInterface;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;

it('returns an empty collection when a municipality has no contracts', function () {
    $references = (new MunicipalityContractResolver(contractRegisterRepository([])))
        ->resolve(new MunicipalityValueObject('IBOTIRAMA'));

    expect($references)->toBe([]);
});

it('returns one contract reference and preserves its company', function () {
    $references = (new MunicipalityContractResolver(contractRegisterRepository([
        resolverContractEntity('08/2023', '800D'),
    ])))->resolve(new MunicipalityValueObject('IBOTIRAMA'));

    expect($references)->toHaveCount(1)
        ->and($references[0])->toBeInstanceOf(MunicipalityContractReferenceDTO::class)
        ->and($references[0]->contractNumber->value)->toBe('08/2023')
        ->and($references[0]->company)->toBe('800D');
});

it('deduplicates repeated contracts and fills a company from a later row', function () {
    $references = (new MunicipalityContractResolver(contractRegisterRepository([
        resolverContractEntity('08 / 2023', null),
        resolverContractEntity('08/2023', '800D'),
        resolverContractEntity('08/2023', 'Outra empresa'),
    ])))->resolve(new MunicipalityValueObject('IBOTIRAMA'));

    expect($references)->toHaveCount(1)
        ->and($references[0]->contractNumber->value)->toBe('08/2023')
        ->and($references[0]->company)->toBe('800D');
});

it('returns every distinct contract from the official contract register', function () {
    $references = (new MunicipalityContractResolver(contractRegisterRepository([
        resolverContractEntity('08/2023', '800D'),
        resolverContractEntity('47/2025', 'Empresa X'),
    ])))->resolve(new MunicipalityValueObject('FEIRA DE SANTANA'));

    expect($references)->toHaveCount(2)
        ->and(array_map(
            static fn (MunicipalityContractReferenceDTO $reference): string => $reference->contractNumber->value,
            $references,
        ))->toBe(['08/2023', '47/2025']);
});

/**
 * @param  list<ContractEntity>  $contracts
 */
function contractRegisterRepository(array $contracts): ContractRepositoryInterface
{
    return new class($contracts) implements ContractRepositoryInterface
    {
        /**
         * @param  list<ContractEntity>  $contracts
         */
        public function __construct(private array $contracts) {}

        public function findByContractNumber(ContractNumberValueObject $contractNumber): ?ContractEntity
        {
            return null;
        }

        public function findBySeiProcess(string $seiProcess): ?ContractEntity
        {
            return null;
        }

        /**
         * @return list<ContractEntity>
         */
        public function findByMunicipality(MunicipalityValueObject $municipality): array
        {
            return $this->contracts;
        }

        /**
         * @return list<ContractEntity>
         */
        public function findByCompany(string $company): array
        {
            return [];
        }
    };
}

function resolverContractEntity(string $contractNumber, ?string $company): ContractEntity
{
    return new ContractEntity(
        contractNumber: $contractNumber,
        company: $company,
        seiProcess: null,
    );
}
