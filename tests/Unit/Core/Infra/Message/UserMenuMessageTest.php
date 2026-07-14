<?php

use App\Core\Conversation\Infra\Message\UserMenuMessage;

it('builds the user menu message', function () {
    $menu = new UserMenuMessage;

    $expectedMessage = <<<'TEXT'
Olá! Eu sou o Assistente da CEIRF.

Como posso ajudar?

1 - Consultar o Painel de Obras
2 - Enviar Relatório de Viagem
3 - Consultar Relatório de Viagem
4 - Informações sobre o assistente
0 - Encerrar atendimento

Digite o número da opção desejada.
TEXT;

    expect($menu->content())->toBe($expectedMessage);
});

it('validates available user menu options without exposing presentation rules to the domain', function () {
    $menu = new UserMenuMessage;

    expect($menu->accepts('2'))->toBeTrue()
        ->and($menu->accepts(' 0 '))->toBeTrue()
        ->and($menu->accepts('9'))->toBeFalse()
        ->and($menu->accepts('Enviar Relatório de Viagem'))->toBeFalse();
});
