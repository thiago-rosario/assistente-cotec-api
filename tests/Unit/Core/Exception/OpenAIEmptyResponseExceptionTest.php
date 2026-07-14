<?php

use App\Core\Conversation\Enum\CodeExceptionEnum;
use App\Core\Conversation\Exception\OpenAIEmptyResponseException;

it('defines openai empty response exception defaults', function () {
    $exception = new OpenAIEmptyResponseException;

    expect($exception)->toBeInstanceOf(RuntimeException::class)
        ->and($exception->getMessage())->toBe('A OpenAI não retornou conteúdo para o prompt informado.')
        ->and($exception->getCode())->toBe(CodeExceptionEnum::OpenAIEmptyResponse->value);
});
