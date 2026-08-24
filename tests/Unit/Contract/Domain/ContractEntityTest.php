<?php

use App\Contract\Domain\Entity\ContractEntity;

it('matches a contract municipality without changing the stored values', function () {
    $contract = new ContractEntity(
        contractNumber: '08/2023',
        company: 'Empresa X',
        seiProcess: null,
        municipalities: ['Feira de Santana'],
    );

    expect($contract->isRelatedToMunicipality('  FEIRA DE SANTANA '))->toBeTrue()
        ->and($contract->isRelatedToMunicipality('Ibotirama'))->toBeFalse()
        ->and($contract->hasCompany())->toBeTrue();
});
