<?php

use App\Core\Infra\Service\WhatsappMessageResponseFormatter;

it('returns every technical notebook field for all municipality records', function () {
    $result = (new WhatsappMessageResponseFormatter)->format(
        intent: 'search_technical_notebook',
        filters: ['municipality' => 'Antas'],
        result: [
            'term' => null,
            'total' => 4,
            'data' => [
                technicalNotebookFormatterRecord(),
                [
                    'process' => '020.4487.2021.0009715-40',
                    'claim' => 'AMPLIACAO',
                    'landStatus' => 'APROVADO',
                ],
                [
                    'process' => '020.4487.2021.0009716-20',
                    'claim' => 'REFORMA',
                    'claimStage' => 'ANALISE',
                ],
                [
                    'process' => '020.4487.2021.0009717-10',
                    'claim' => 'CONCLUSAO',
                    'buildStatus' => 'CONCLUIDO',
                ],
            ],
        ],
    );

    expect($result['reply'])
        ->toContain('Encontrei 4 registros para o município ANTAS.')
        ->toContain('1. Registro do Caderno Técnico')
        ->toContain('   Item: 1')
        ->toContain('   Etapa: Planejamento')
        ->toContain('   Município: Antas')
        ->toContain('   Processo: 020.4487.2021.0009714-69')
        ->toContain('   Força: PC')
        ->toContain('   Pleito: CONSTRUCAO')
        ->toContain('   Tipologia: 1B')
        ->toContain('   Obs. tipologia: Observação')
        ->toContain('   Valor estimado: R$ 1.539.740,33')
        ->toContain('   Vistoria: Realizada')
        ->toContain('   Relatório SEI: SEI-123')
        ->toContain('   Status do terreno: APROVADO')
        ->toContain('   Regularização fundiária: Regularizado')
        ->toContain('   Estudo de solo: Concluído')
        ->toContain('   Ambiental: Licenciado')
        ->toContain('   Comentário da fiscalização: Sem pendências')
        ->toContain('   Etapa pleito: ANALISE')
        ->toContain('   SEI licitação: SEI-LIC-123')
        ->toContain('   Contrato: Contrato 123')
        ->toContain('   Instrumento FIPLAN: Fiplan 123')
        ->toContain('   Status de obra: LICITADO')
        ->toContain('   Data de inauguração: Não informado')
        ->toContain('4. Registro do Caderno Técnico')
        ->toContain('   Processo: 020.4487.2021.0009717-10')
        ->not->toContain('Mostrei os primeiros resultados.');
});

it('returns every technical notebook field when searching by sei process', function () {
    $result = (new WhatsappMessageResponseFormatter)->format(
        intent: 'search_technical_notebook',
        filters: ['process' => '020.4487.2021.0009714-69'],
        result: [
            'term' => null,
            'total' => 1,
            'data' => [
                technicalNotebookFormatterRecord(),
            ],
        ],
    );

    expect($result['reply'])
        ->toContain('Encontrei 1 registro para o município ANTAS.')
        ->toContain('1. Registro do Caderno Técnico')
        ->toContain('   Item: 1')
        ->toContain('   Etapa: Planejamento')
        ->toContain('   Município: Antas')
        ->toContain('   Processo: 020.4487.2021.0009714-69')
        ->toContain('   Força: PC')
        ->toContain('   Pleito: CONSTRUCAO')
        ->toContain('   Tipologia: 1B')
        ->toContain('   Obs. tipologia: Observação')
        ->toContain('   Valor estimado: R$ 1.539.740,33')
        ->toContain('   Vistoria: Realizada')
        ->toContain('   Relatório SEI: SEI-123')
        ->toContain('   Status do terreno: APROVADO')
        ->toContain('   Regularização fundiária: Regularizado')
        ->toContain('   Estudo de solo: Concluído')
        ->toContain('   Ambiental: Licenciado')
        ->toContain('   Comentário da fiscalização: Sem pendências')
        ->toContain('   Etapa pleito: ANALISE')
        ->toContain('   SEI licitação: SEI-LIC-123')
        ->toContain('   Contrato: Contrato 123')
        ->toContain('   Instrumento FIPLAN: Fiplan 123')
        ->toContain('   Status de obra: LICITADO')
        ->toContain('   Data de inauguração: Não informado');
});

it('keeps empty response payload shape consistent', function () {
    $result = (new WhatsappMessageResponseFormatter)->rateLimited();

    expect($result)->toBe([
        'reply' => 'Recebi sua mensagem, mas o serviço de interpretação está temporariamente no limite. Tente novamente em alguns instantes.',
        'intent' => 'rate_limited',
        'total' => 0,
        'data' => [],
        'filters' => [],
    ]);
});

/**
 * @return array<string, mixed>
 */
function technicalNotebookFormatterRecord(): array
{
    return [
        'item' => 1,
        'stage' => 'Planejamento',
        'municipality' => 'Antas',
        'process' => '020.4487.2021.0009714-69',
        'force' => 'PC',
        'claim' => 'CONSTRUCAO',
        'typology' => '1B',
        'typologyObservation' => 'Observação',
        'estimatedValue' => 1539740.33,
        'inspection' => 'Realizada',
        'seiReport' => 'SEI-123',
        'landStatus' => 'APROVADO',
        'landRegularization' => 'Regularizado',
        'soilStudy' => 'Concluído',
        'environmental' => 'Licenciado',
        'inspectionComment' => 'Sem pendências',
        'claimStage' => 'ANALISE',
        'biddingSei' => 'SEI-LIC-123',
        'contract' => 'Contrato 123',
        'fiplanInstrument' => 'Fiplan 123',
        'buildStatus' => 'LICITADO',
        'inaugurationDate' => null,
    ];
}
