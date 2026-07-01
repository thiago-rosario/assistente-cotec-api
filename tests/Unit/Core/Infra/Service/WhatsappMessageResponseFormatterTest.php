<?php

use App\Core\Infra\Message\FoundRecordsReplyBuilder;
use App\Core\Infra\Message\TechnicalNotebookReplyBuilder;
use App\Core\Infra\Message\WhatsappDefaultReplies;
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

    $reply = $result['reply'];

    expect($reply)
        ->toContain('Encontrei 4 registros para o município ANTAS.')
        ->toContain('📌 Registro 1 de 4 — PAINEL DE OBRAS')
        ->toContain('• Item: 1')
        ->toContain('• Etapa: Planejamento')
        ->toContain('• Município: Antas')
        ->toContain('• Processo: 020.4487.2021.0009714-69')
        ->toContain('• Força: PC')
        ->toContain('• Pleito: CONSTRUCAO')
        ->toContain('• Tipologia: 1B')
        ->toContain('• Obs. tipologia: Observação')
        ->toContain('• Valor estimado: R$ 1.539.740,33')
        ->toContain('• Vistoria: Realizada')
        ->toContain('• Relatório SEI: SEI-123')
        ->toContain('• Status do terreno: APROVADO')
        ->toContain('• Regularização fundiária: Regularizado')
        ->toContain('• Estudo de solo: Concluído')
        ->toContain('• Ambiental: Licenciado')
        ->toContain('• Comentário da fiscalização: Sem pendências')
        ->toContain('• Etapa pleito: ANALISE')
        ->toContain('• SEI licitação: SEI-LIC-123')
        ->toContain('• Contrato: Contrato 123')
        ->toContain('• Instrumento FIPLAN: Fiplan 123')
        ->toContain('• Status de obra: LICITADO')
        ->toContain('• Data de inauguração: Não informado')
        ->toContain('📌 Registro 4 de 4 — PAINEL DE OBRAS')
        ->toContain('• Processo: 020.4487.2021.0009717-10')
        ->not->toContain('Registro do Caderno Técnico')
        ->not->toContain('1. Registro')
        ->not->toContain('2. Item:')
        ->not->toContain('Mostrei os primeiros resultados.');

    expect(substr_count($reply, '────────────'))->toBe(3);
    expect($reply)->not->toMatch('/^\d+\.\s+(Item|Etapa|Município|Processo):/m');
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

    $expectedReply = <<<'TEXT'
Encontrei 1 registro para o município ANTAS.

📌 Registro 1 de 1 — PAINEL DE OBRAS

• Item: 1
• Etapa: Planejamento
• Município: Antas
• Processo: 020.4487.2021.0009714-69
• Força: PC
• Pleito: CONSTRUCAO
• Tipologia: 1B
• Obs. tipologia: Observação
• Valor estimado: R$ 1.539.740,33
• Vistoria: Realizada
• Relatório SEI: SEI-123
• Status do terreno: APROVADO
• Regularização fundiária: Regularizado
• Estudo de solo: Concluído
• Ambiental: Licenciado
• Comentário da fiscalização: Sem pendências
• Etapa pleito: ANALISE
• SEI licitação: SEI-LIC-123
• Contrato: Contrato 123
• Instrumento FIPLAN: Fiplan 123
• Status de obra: LICITADO
• Data de inauguração: Não informado
TEXT;

    expect($result['reply'])->toBe($expectedReply);
    expect($result['reply'])
        ->not->toContain('Registro do Caderno Técnico')
        ->not->toContain('1. Registro')
        ->not->toContain('2. Item:');
});

it('returns no records message while preserving payload filters', function () {
    $result = whatsappMessageResponseFormatter()->format(
        intent: 'search_technical_notebook',
        filters: ['municipality' => 'Antas'],
        result: [
            'term' => null,
            'total' => 0,
            'data' => [],
        ],
    );

    expect($result)->toBe([
        'reply' => 'Não encontrei registros para essa consulta. Tente informar o nome do município ou o número do processo.',
        'intent' => 'search_technical_notebook',
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
            ."• Número do processo\n\n"
            ."O processo pode ser referente à solicitação do pleito, à licitação ou ao contrato.\n\n"
            .'Para um melhor atendimento, envie apenas uma dessas informações por vez.',
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
