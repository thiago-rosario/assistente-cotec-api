<?php

use App\Core\Enum\CodeExceptionEnum;
use App\Core\Exception\GoogleSheetNotConfiguredException;
use App\Core\Exception\MessageNotContentException;
use App\Core\Exception\OpenAIEmptyResponseException;
use App\Core\Infra\External\OpenAI\OpenAIResponseService;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Responses\CreateResponse;
use OpenAI\Testing\Enums\OverrideStrategy;

it('defines message content exception as a runtime exception', function () {
    $exception = new MessageNotContentException;

    expect($exception)->toBeInstanceOf(RuntimeException::class)
        ->and($exception->getMessage())->toBe('Payload de mensagem recebido sem conteúdo.')
        ->and($exception->getCode())->toBe(CodeExceptionEnum::MessageNotContent->value);
});

it('defines openai empty response exception defaults', function () {
    $exception = new OpenAIEmptyResponseException;

    expect($exception)->toBeInstanceOf(RuntimeException::class)
        ->and($exception->getMessage())->toBe('A OpenAI não retornou conteúdo para o prompt informado.')
        ->and($exception->getCode())->toBe(CodeExceptionEnum::OpenAIEmptyResponse->value);
});

it('defines google sheet not configured exception defaults', function () {
    $exception = new GoogleSheetNotConfiguredException(sheetId: 999);

    expect($exception)->toBeInstanceOf(RuntimeException::class)
        ->and($exception->sheetId)->toBe(999)
        ->and($exception->getMessage())->toBe('A aba informada não está configurada para consulta.')
        ->and($exception->getCode())->toBe(CodeExceptionEnum::GoogleSheetNotConfigured->value);
});

it('throws the openai empty response exception when responses api returns no text', function () {
    OpenAI::fake([
        CreateResponse::fake(['output' => []], strategy: OverrideStrategy::Replace),
    ]);

    expect(fn () => (new OpenAIResponseService)->generateResponse('Responda olá.'))
        ->toThrow(OpenAIEmptyResponseException::class, 'A OpenAI não retornou conteúdo para o prompt informado.');
});
