<?php

declare(strict_types=1);

namespace App\Core\Infra\External\OpenAI;

use App\Core\Application\Interfaces\Service\OpenAIServiceInterface;
use App\Core\Exception\OpenAIEmptyResponseException;
use OpenAI\Laravel\Facades\OpenAI;

/**
 * Implementa a geração de respostas usando a Responses API da OpenAI.
 */
class OpenAIResponseService implements OpenAIServiceInterface
{
    /**
     * Envia o prompt para a OpenAI e retorna o texto gerado.
     *
     * @throws OpenAIEmptyResponseException Quando a OpenAI responde sem conteúdo textual.
     */
    public function generateResponse(string $prompt): string
    {
        $response = OpenAI::responses()->create([
            'model' => 'gpt-5',
            'input' => $prompt,
        ]);

        $content = trim((string) $response->outputText);

        if ($content === '') {
            throw new OpenAIEmptyResponseException;
        }

        return $content;
    }
}
