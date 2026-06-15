<?php

use App\Core\Infra\Message\FoundRecordsReplyBuilder;
use App\Core\Infra\Message\GenericRecordsReplyBuilder;
use App\Core\Infra\Message\TechnicalNotebookReplyBuilder;
use App\Core\Infra\Message\WhatsappDefaultReplies;
use App\Core\Infra\Message\WhatsappIntentLabel;
use App\Core\Infra\Message\WhatsappRecordValueFormatter;
use App\Core\Infra\Message\WhatsappResponsePayloadFactory;
use App\Core\Infra\Service\WhatsappMessageResponseFormatter;

it('returns every technical notebook field for all municipality records', function () {
    $result = whatsappMessageResponseFormatter()->format(
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
    $result = whatsappMessageResponseFormatter()->format(
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

it('summarizes generic records and suggests refinement when results are limited', function () {
    $result = whatsappMessageResponseFormatter()->format(
        intent: 'search_construction_demand',
        filters: ['municipality' => 'Feira de Santana'],
        result: [
            'term' => null,
            'total' => 4,
            'data' => [
                [
                    'process' => 'PROC-1',
                    'municipality' => 'Feira de Santana',
                    'force' => 'PM',
                    'region' => 'Norte',
                    'landStatus' => 'Regular',
                    'progress' => 'Em análise',
                    'buildStatus' => 'Licitado',
                    'requester' => 'COTEC',
                ],
                [
                    'process' => 'PROC-2',
                    'municipality' => 'Feira de Santana',
                ],
                [
                    'unknown' => 'Sem campos conhecidos',
                ],
                [
                    'process' => 'PROC-4',
                    'municipality' => 'Feira de Santana',
                ],
            ],
        ],
    );

    expect($result['reply'])
        ->toContain('Encontrei 4 registro(s) em demandas de construção.')
        ->toContain('1. Processo: PROC-1 | Município: Feira de Santana | Força: PM | Região: Norte | Terreno: Regular | Andamento: Em análise | Construção: Licitado | Solicitante: COTEC')
        ->toContain('2. Processo: PROC-2 | Município: Feira de Santana')
        ->toContain('3. Registro sem resumo disponível.')
        ->toContain('Mostrei os primeiros resultados. Refine a busca para localizar um registro específico.')
        ->not->toContain('4. Processo: PROC-4');
});

it('returns no records message while preserving payload filters', function () {
    $result = whatsappMessageResponseFormatter()->format(
        intent: 'search_land_survey',
        filters: ['municipality' => 'Antas'],
        result: [
            'term' => null,
            'total' => 0,
            'data' => [],
        ],
    );

    expect($result)->toBe([
        'reply' => 'Não encontrei registros para essa consulta. Tente informar o número do processo, município, força, região ou situação.',
        'intent' => 'search_land_survey',
        'total' => 0,
        'data' => [],
        'filters' => ['municipality' => 'Antas'],
    ]);
});

it('keeps empty response payload shape consistent', function () {
    $result = whatsappMessageResponseFormatter()->rateLimited();

    expect($result)->toBe([
        'reply' => 'Recebi sua mensagem, mas o serviço de interpretação está temporariamente no limite. Tente novamente em alguns instantes.',
        'intent' => 'rate_limited',
        'total' => 0,
        'data' => [],
        'filters' => [],
    ]);
});

it('returns the COTEC welcome message for greetings', function () {
    $result = whatsappMessageResponseFormatter()->greeting();

    expect($result)->toBe([
        'reply' => "Olá! Eu sou o assistente da COTEC.\n\n"
            ."Posso te ajudar a consultar informações do *Painel de Obras da CEIRF/SSP*.\n\n"
            ."Para iniciar a consulta, envie uma das opções abaixo:\n\n"
            ."• Nome do município\n"
            ."• Número do processo de solicitação do pleito\n"
            ."• Número do processo da licitação ou do contrato\n\n"
            .'Quanto mais direto for o envio, melhor será o atendimento.',
        'intent' => 'greeting',
        'total' => 0,
        'data' => [],
        'filters' => [],
    ]);
});

function whatsappMessageResponseFormatter(): WhatsappMessageResponseFormatter
{
    $valueFormatter = new WhatsappRecordValueFormatter;

    return new WhatsappMessageResponseFormatter(
        new WhatsappDefaultReplies,
        new WhatsappResponsePayloadFactory,
        new FoundRecordsReplyBuilder(
            new TechnicalNotebookReplyBuilder($valueFormatter),
            new GenericRecordsReplyBuilder(new WhatsappIntentLabel, $valueFormatter),
        ),
    );
}

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
