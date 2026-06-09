<?php

use App\Core\Domain\Entity\LandSurveyEntity;

it('represents a land survey row from the backup sheet', function () {
    $survey = new LandSurveyEntity(
        municipality: 'Alcobaça',
        process: '030.2709.2022.0197573-43',
        region: 'Sul',
        unitSizeClaim: 'CIPM',
        force: 'PM',
        requester: 'Comando Região Sul',
        ownership: null,
        topography: 'Levantamento recebido',
        landStatus: 'Aguardando visita técnica',
        progress: 'Entrar em contato com prepostos da Prefeitura.',
        municipalityFocalPointContact: 'Prefeito Givaldo Muniz (73) 99926-2900',
        militaryPoliceFocalPointContact: 'Maj Marion (71)9959-6811',
        civilPoliceFocalPointContact: null,
        documentationLink: 'https://example.com/documentacao',
        updatedAt: new DateTimeImmutable('2026-05-01'),
        observations: 'Doado',
        requestedAt: new DateTimeImmutable('2019-03-20'),
    );

    expect($survey->hasDocumentationLink())->toBeTrue()
        ->and($survey->hasPoliceFocalPointContact())->toBeTrue()
        ->and($survey->hasTopography())->toBeTrue()
        ->and($survey->toSearchableArray())->toContain('Alcobaça', '030.2709.2022.0197573-43', 'Levantamento recebido');
});
