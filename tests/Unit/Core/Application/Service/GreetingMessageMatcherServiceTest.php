<?php

use App\Core\Application\Service\GreetingMessageMatcherService;

it('matches possible greeting messages', function (string $message) {
    expect((new GreetingMessageMatcherService)->matches($message))->toBeTrue();
})->with([
    'oi' => 'oi',
    'oi duplicated with whatsapp timestamps' => "Oi\nOi\n16:52\n16:52\n16:52",
    'oi prolongado' => 'Oiii',
    'ola com acento e pontuacao' => 'Olá!',
    'bom dia' => 'bom dia',
    'boa tarde' => 'Boa tarde.',
    'boa noite' => 'boa noite',
    'oie' => 'oie',
    'alo' => 'alô',
    'opa' => 'Opa!',
    'e ai' => 'E aí?',
    'eae' => 'eae',
    'salve' => 'salve',
    'hello' => 'hello',
    'hi' => 'hi',
    'oi bom dia' => 'Oi, bom dia!',
    'ola boa tarde' => 'Olá, boa tarde!',
    'tudo bem' => 'Tudo bem?',
    'tudo bom abreviado' => 'td bom',
]);

it('does not match non greeting messages', function (string $message) {
    expect((new GreetingMessageMatcherService)->matches($message))->toBeFalse();
})->with([
    'empty' => '',
    'process query' => 'Quero consultar o processo 020.4487.2021.0009714-69',
    'municipality query' => 'Bom dia, quero consultar o município de Antas',
    'technical notebook query' => 'Existe obra em Antas?',
]);
