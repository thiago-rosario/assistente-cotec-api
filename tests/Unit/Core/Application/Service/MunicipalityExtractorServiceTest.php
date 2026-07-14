<?php

use App\Core\Conversation\Application\Service\MunicipalityExtractorService;

it('extracts standalone municipality names without using ai', function (string $message, string $expectedMunicipality) {
    expect((new MunicipalityExtractorService)->extract($message))->toBe($expectedMunicipality);
})->with([
    'uppercase without accent' => ['ANDARAI', 'ANDARAI'],
    'uppercase with accent' => ['ANDARAÍ', 'ANDARAÍ'],
    'uppercase with greeting' => ['Bom dia, ANDARAÍ', 'ANDARAÍ'],
    'title case with greeting' => ['Olá, Andaraí!', 'Andaraí'],
    'compound greeting' => ['Oi, bom dia, São Francisco do Conde', 'São Francisco do Conde'],
    'title case with accent' => ['Andaraí', 'Andaraí'],
    'lowercase without accent' => ['andarai', 'andarai'],
    'minor typo with accent' => ['andarí', 'andarí'],
    'title case without accent' => ['Andarai', 'Andarai'],
]);

it('keeps non municipality questions out of direct standalone extraction', function () {
    expect((new MunicipalityExtractorService)->extract('Qual é a previsão do tempo?'))->toBeNull();
});

it('keeps standalone greetings out of direct municipality extraction', function (string $message) {
    expect((new MunicipalityExtractorService)->extract($message))->toBeNull();
})->with([
    'bom dia' => 'Bom dia',
    'ola' => 'Olá!',
    'oi bom dia' => 'Oi, bom dia!',
]);
