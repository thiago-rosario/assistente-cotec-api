<?php

use App\Core\BuildPanel\Infra\Message\WhatsappDefaultReplies;
use App\Core\Conversation\Application\Interfaces\Service\WhatsappDefaultRepliesInterface;
use App\Core\Conversation\Infra\Message\UserMenuMessage;

it('uses the core user menu as build panel greeting', function () {
    $replies = new WhatsappDefaultReplies(new UserMenuMessage);

    expect($replies)
        ->toBeInstanceOf(WhatsappDefaultRepliesInterface::class)
        ->and($replies->greeting())->toBe((new UserMenuMessage)->content());
});

it('keeps build panel consultation fallback replies', function () {
    $replies = new WhatsappDefaultReplies(new UserMenuMessage);

    expect($replies->noRecords())
        ->toBe('Não encontrei registros para essa consulta. Tente informar o nome do município ou o número do processo.')
        ->and($replies->buildPanelConsultation())
        ->toBe(<<<'TEXT'
Olá! Você acessou o módulo *Painel de Obras*.

Aqui você pode consultar informações sobre obras por meio de uma destas opções:

• Nome do município
• Número do processo SEI

Envie apenas uma informação por vez para iniciar a consulta.
TEXT)
        ->and($replies->unknownIntent())
        ->toBe('Não consegui identificar exatamente qual consulta você deseja fazer. Envie o número de uma opção do menu ou informe o nome do município ou o número do processo.')
        ->and($replies->error())
        ->toBe('Não consegui processar sua solicitação agora. Tente novamente informando uma opção do menu, o nome do município ou o número do processo.');
});
