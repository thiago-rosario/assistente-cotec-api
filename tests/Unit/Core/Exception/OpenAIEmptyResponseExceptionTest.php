<?php

use App\Core\Enum\CodeExceptionEnum;
use App\Core\Exception\OpenAIEmptyResponseException;

it('defines openai empty response exception defaults', function () {
    $exception = new OpenAIEmptyResponseException;

    expect($exception)->toBeInstanceOf(RuntimeException::class)
        ->and($exception->getMessage())->toBe('A OpenAI não retornou conteúdo para o prompt informado.')
        ->and($exception->getCode())->toBe(CodeExceptionEnum::OpenAIEmptyResponse->value);
});
