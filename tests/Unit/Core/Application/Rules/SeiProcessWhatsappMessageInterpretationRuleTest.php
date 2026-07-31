<?php

use App\BuildPanel\Application\Rules\SeiProcessWhatsappMessageInterpretationRule;

it('interprets messages with sei process number', function () {
    $interpretation = (new SeiProcessWhatsappMessageInterpretationRule)(
        'Quero consultar o processo 020.4487.2021.0009714-69',
    );

    expect($interpretation)
        ->not->toBeNull()
        ->and($interpretation->intent)->toBe('search_technical_notebook')
        ->and($interpretation->filters)->toBe([
            'process' => '020.4487.2021.0009714-69',
        ]);
});

it('returns null when message does not have sei process number', function () {
    expect((new SeiProcessWhatsappMessageInterpretationRule)('Quero consultar Antas'))->toBeNull();
});
