<?php

use App\Core\Infra\Message\FoundRecordsReplyBuilder;
use App\Core\Infra\Message\TechnicalNotebookReplyBuilder;
use App\Core\Infra\Message\WhatsappRecordValueFormatter;

it('builds found records replies using the technical notebook format', function () {
    $reply = (new FoundRecordsReplyBuilder(
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
