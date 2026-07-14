<?php

use App\Core\Conversation\Enum\WhatsappMessageIntentEnum;
use App\Core\Conversation\Infra\Parser\WhatsappMessageInterpretationParser;

it('parses whatsapp message interpretations into dto', function () {
    $interpretation = (new WhatsappMessageInterpretationParser)->parse(
        '{"intent":"search-technical-notebook","filters":{"municipio":"Antas","land_status":"Aprovado","empty":" "}}',
    );

    expect($interpretation->intent)->toBe(WhatsappMessageIntentEnum::SEARCH_TECHNICAL_NOTEBOOK->value)
        ->and($interpretation->filters)->toBe([
            'municipality' => 'Antas',
            'landStatus' => 'Aprovado',
        ]);
});

it('normalizes unsupported intents to unknown', function () {
    $interpretation = (new WhatsappMessageInterpretationParser)->parse([
        'intent' => 'unsupported-intent',
        'filters' => ['municipio' => 'Antas'],
    ]);

    expect($interpretation->intent)->toBe(WhatsappMessageIntentEnum::UNKNOWN->value)
        ->and($interpretation->filters)->toBe([
            'municipality' => 'Antas',
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
