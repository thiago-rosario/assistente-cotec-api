<?php

use App\Core\Conversation\Exception\OpenAIEmptyResponseException;
use App\Core\Conversation\Infra\Service\InterpretWhatsappMessageWithAiService;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Responses\CreateResponse;
use OpenAI\Testing\Enums\OverrideStrategy;
use Tests\TestCase;

uses(TestCase::class);

it('throws the openai empty response exception when responses api returns no text', function () {
    OpenAI::fake([
        CreateResponse::fake(['output' => []], strategy: OverrideStrategy::Replace),
    ]);

    expect(fn () => (new InterpretWhatsappMessageWithAiService)('Responda olá.'))
        ->toThrow(OpenAIEmptyResponseException::class, 'A OpenAI não retornou conteúdo para o prompt informado.');
});
