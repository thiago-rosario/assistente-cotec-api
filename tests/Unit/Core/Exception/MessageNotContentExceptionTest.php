<?php

use App\Core\Enum\CodeExceptionEnum;
use App\Core\Exception\MessageNotContentException;

it('defines message content exception as a runtime exception', function () {
    $exception = new MessageNotContentException;

    expect($exception)->toBeInstanceOf(RuntimeException::class)
        ->and($exception->getMessage())->toBe('Payload de mensagem recebido sem conteúdo.')
        ->and($exception->getCode())->toBe(CodeExceptionEnum::MessageNotContent->value);
});
