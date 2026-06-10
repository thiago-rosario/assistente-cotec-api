<?php

use App\Core\Enum\WhatsappMessageIntentEnum;
use App\Core\Infra\Parser\WhatsappMessageInterpretationParser;

it('parses whatsapp message interpretations into dto', function () {
    $interpretation = (new WhatsappMessageInterpretationParser)->parse(
        '{"intent":"land-survey","filters":{"municipio":"Antas","land_status":"Aprovado","empty":" "}}',
    );

    expect($interpretation->intent)->toBe('search_land_survey')
        ->and($interpretation->filters)->toBe([
            'municipality' => 'Antas',
            'landStatus' => 'Aprovado',
        ]);
});

it('normalizes invalid filters to empty array', function () {
    $interpretation = (new WhatsappMessageInterpretationParser)->parse([
        'intent' => 'unknown',
        'filters' => 'Antas',
    ]);

    expect($interpretation->intent)->toBe(WhatsappMessageIntentEnum::UNKNOWN->value)
        ->and($interpretation->filters)->toBe([]);
});
