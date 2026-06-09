<?php

use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Infra\Mapper\LandSurveySheetMapper;

it('maps a land survey spreadsheet row to the domain entity', function () {
    $entity = (new LandSurveySheetMapper)->fromRow([
        'MUNICIPIO' => 'Alcobaça',
        'PROCESSO SEI' => '030.2709.2022.0197573-43',
        'REGIÃO (RISP 2023)' => 'Sul',
        'PLEITO UNIDADE TAMANHO' => 'CIPM',
        'FORÇA' => 'PM',
        'REQUISITANTE' => 'Comando Região Sul',
        'TITULARIDADE' => '',
        'TOPOGRAFIA' => 'Levantamento recebido',
        'SITUAÇÃO DO TERRENO' => 'Aguardando visita técnica',
        'ANDAMENTO' => 'Entrar em contato com prepostos da Prefeitura.',
        'CONTATO - PONTO FOCAL MUNICÍPIO' => 'Prefeito Givaldo Muniz (73) 99926-2900',
        'PONTO FOCAL POLÍCIA MILITAR' => 'Maj Marion (71)9959-6811',
        'PONTO FOCAL POLÍCIA CIVIL' => '',
        'LINK PARA DOCUMENTAÇÃO' => 'https://example.com/documentacao',
        'ATUALIZADO EM' => '01/05/2026',
        'Observaçoes' => 'Doado',
        'Data da solicitação' => '20/03/2019',
    ]);

    expect($entity)->toBeInstanceOf(LandSurveyEntity::class)
        ->and($entity->municipality)->toBe('Alcobaça')
        ->and($entity->ownership)->toBeNull()
        ->and($entity->updatedAt?->format('Y-m-d'))->toBe('2026-05-01')
        ->and($entity->requestedAt?->format('Y-m-d'))->toBe('2019-03-20');
});
