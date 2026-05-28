<?php

declare(strict_types=1);

namespace App\Core\Infra\External\OpenAI;

use App\Core\Application\Interfaces\OpenAIServiceInterface;
use OpenAI\Laravel\Facades\OpenAI;
use RuntimeException;

class OpenAIResponseService implements OpenAIServiceInterface
{
    public function generateResponse(string $prompt): string
    {
        $response = OpenAI::responses()->create([
            'model' => 'gpt-5',
            'input' => $prompt,
        ]);

        $content = trim((string) $response->outputText);

        if ($content === '') {
            throw new RuntimeException('A OpenAI não retornou conteúdo para o prompt informado.');
        }

        return $content;
    }
}
