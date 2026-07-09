<?php

use App\BuildPanel\Infra\Message\TechnicalNotebookFoundRecordsReplyBuilder;
use App\BuildPanel\Infra\Message\TechnicalNotebookReplyBuilder;
use App\BuildPanel\Infra\Message\WhatsappRecordValueFormatter;

it('builds found records replies using the technical notebook format', function () {
    $reply = (new TechnicalNotebookFoundRecordsReplyBuilder(
        new TechnicalNotebookReplyBuilder(new WhatsappRecordValueFormatter),
    ))->build(
        filters: ['municipality' => 'Antas'],
        result: [
            'term' => null,
            'total' => 1,
            'data' => [
                [
                    'municipality' => 'Antas',
                    'process' => '020.4487.2021.0009714-69',
                ],
            ],
        ],
    );

    expect($reply)
        ->toContain('Encontrei 1 registro para o município ANTAS.')
        ->toContain('Registro 1 de 1')
        ->toContain('PAINEL DE OBRAS')
        ->toContain('• Processo: 020.4487.2021.0009714-69');
});

it('supports only the technical notebook whatsapp intent', function () {
    $builder = new TechnicalNotebookFoundRecordsReplyBuilder(
        new TechnicalNotebookReplyBuilder(new WhatsappRecordValueFormatter),
    );

    expect($builder->supports('search_technical_notebook'))->toBeTrue()
        ->and($builder->supports('unsupported_intent'))->toBeFalse();
});
