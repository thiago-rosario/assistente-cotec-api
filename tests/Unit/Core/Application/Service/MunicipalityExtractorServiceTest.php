<?php

it('extracts standalone municipality names without using ai', function (string $message, string $expectedMunicipality) {
    expect(municipalityExtractorForTests()->extract($message))->toBe($expectedMunicipality);
})->with([
    'uppercase without accent' => ['ANDARAI', 'Andaraí'],
    'uppercase with accent' => ['ANDARAÍ', 'Andaraí'],
    'uppercase with greeting' => ['Bom dia, ANDARAÍ', 'Andaraí'],
    'title case with greeting' => ['Olá, Andaraí!', 'Andaraí'],
    'compound greeting' => ['Oi, bom dia, São Francisco do Conde', 'São Francisco do Conde'],
    'title case with accent' => ['Andaraí', 'Andaraí'],
    'lowercase without accent' => ['andarai', 'Andaraí'],
    'minor typo with accent' => ['andarí', 'Andaraí'],
    'title case without accent' => ['Andarai', 'Andaraí'],
    'consult query' => ['Quero consultar Antas', 'Antas'],
    'consult municipality query' => ['Quero consultar o município de Antas', 'Antas'],
    'technical notebook query' => ['Caderno técnico de São Francisco do Conde', 'São Francisco do Conde'],
]);

it('keeps non municipality questions out of direct standalone extraction', function () {
    expect(municipalityExtractorForTests()->extract('Qual é a previsão do tempo?'))->toBeNull();
});

it('keeps unsupported filter queries out of direct municipality extraction', function (string $message) {
    expect(municipalityExtractorForTests()->extract($message))->toBeNull();
})->with([
    'force' => 'Quero consultar por força PC',
    'land survey' => 'Buscar levantamento de terreno em Antas',
    'process label without number' => 'Consulta processo',
]);

it('keeps standalone greetings out of direct municipality extraction', function (string $message) {
    expect(municipalityExtractorForTests()->extract($message))->toBeNull();
})->with([
    'bom dia' => 'Bom dia',
    'ola' => 'Olá!',
    'oi bom dia' => 'Oi, bom dia!',
]);

it('recognizes only available municipalities with conservative typo matching', function (string $message, ?string $expected) {
    expect(municipalityExtractorForTests()->extract($message))->toBe($expected);
})->with([
    ['Varzea Grande', 'Várzea Grande'],
    ['varzea grande', 'Várzea Grande'],
    ['VÁRZEA GRANDE', 'Várzea Grande'],
    ['  Varzea   Grande  ', 'Várzea Grande'],
    ['Varzia grande', 'Várzea Grande'],
    ['Qualquer coisa', null],
    ['Entrada 1', null],
    ['Teste', null],
    ['abcdef', null],
    ['123', null],
    ['Município de Qualquer coisa', null],
    ['Consultar abcdef', null],
    ['Salvador', 'Salvador'],
    ['Feira de Santana', 'Feira de Santana'],
    ['Várzea Grande', 'Várzea Grande'],
    ['Varzio Grandi', null],
    ['teste', null],
    ['banana', null],
    ['quero consultar contrato', null],
    ['contrato', null],
    ['empresa conder', null],
    ['me mostre contratos', null],
    ['123456', null],
    ['52/2022', null],
    ['020.4487.2023.0000620-96', null],
    ['R$ 100.000,00', null],
]);

it('rejects ambiguous and short fuzzy matches but prioritizes exact matches', function () {
    $extractor = municipalityExtractorForTests(['Antas', 'Antes', 'Una']);

    expect($extractor->extract('Antos'))->toBeNull()
        ->and($extractor->extract('Antas'))->toBe('Antas')
        ->and($extractor->extract('Uma'))->toBeNull();
});

it('uses contract municipalities and deduplicates normalized names across sources', function () {
    $extractor = municipalityExtractorForTests(['Várzea Grande'], ['VARZEA GRANDE', 'Ibotirama']);

    expect($extractor->extract('Varzia grande'))->toBe('Várzea Grande')
        ->and($extractor->extract('Ibotirama'))->toBe('Ibotirama')
        ->and($extractor->extract('Ibotiramo'))->toBe('Ibotirama');
});

it('rejects municipalities when the available sources are empty', function () {
    expect(municipalityExtractorForTests([], [])->extract('Varzea Grande'))->toBeNull();
});
