<?php

use App\Core\Conversation\Enum\ConversationCodeExceptionEnum;
use App\Core\Conversation\Exception\MessageNotContentException;

it('defines message content exception as a runtime exception', function () {
    $exception = new MessageNotContentException;

    expect($exception)->toBeInstanceOf(RuntimeException::class)
        ->and($exception->getMessage())->toBe('Payload de mensagem recebido sem conteúdo.')
        ->and($exception->getCode())->toBe(ConversationCodeExceptionEnum::MessageNotContent->value);
});
