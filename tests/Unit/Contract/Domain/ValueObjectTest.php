<?php

use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;

it('normalizes a contract number and compares equivalent values consistently', function () {
    $contractNumber = new ContractNumberValueObject(' 08 / 2023 ');

    expect($contractNumber->value)->toBe('08/2023')
        ->and($contractNumber->equals(new ContractNumberValueObject('08/2023')))->toBeTrue()
        ->and((string) $contractNumber)->toBe('08/2023');
});

it('rejects an empty contract number', function () {
    expect(fn () => new ContractNumberValueObject('   '))
        ->toThrow(InvalidArgumentException::class);
});

it('normalizes a municipality without changing its display value', function () {
    $municipality = new MunicipalityValueObject('  Feira   de Santana ');

    expect($municipality->value)->toBe('Feira de Santana')
        ->and($municipality->normalized)->toBe('FEIRA DE SANTANA')
        ->and($municipality->equals(new MunicipalityValueObject('FEIRA DE SANTANA')))->toBeTrue()
        ->and((string) $municipality)->toBe('Feira de Santana');
});

it('rejects an empty municipality', function () {
    expect(fn () => new MunicipalityValueObject('   '))
        ->toThrow(InvalidArgumentException::class);
});
