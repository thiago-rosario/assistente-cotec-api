<?php

use App\Core\Infra\Service\WhatsappMessageResponseFormatter;

it('limits technical notebook list replies and suggests refining the search', function () {
    $result = (new WhatsappMessageResponseFormatter)->format(
        intent: 'search_technical_notebook',
        filters: ['municipality' => 'Antas'],
        result: [
            'term' => null,
            'total' => 4,
            'data' => [
                [
                    'process' => '020.4487.2021.0009714-69',
                    'claim' => 'CONSTRUCAO',
                    'buildStatus' => 'LICITADO',
                ],
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
        ->toContain('1. Processo: 020.4487.2021.0009714-69')
        ->toContain('2. Processo: 020.4487.2021.0009715-40')
        ->toContain('3. Processo: 020.4487.2021.0009716-20')
        ->not->toContain('4. Processo: 020.4487.2021.0009717-10')
        ->toContain('Mostrei os primeiros resultados. Refine a busca para localizar um registro específico.');
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
