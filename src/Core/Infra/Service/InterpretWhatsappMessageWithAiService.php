<?php

declare(strict_types=1);

namespace App\Core\Infra\Service;

use App\Core\Application\Interfaces\InterpretWhatsappMessageWithAiServiceInterface;
use App\Core\Exception\OpenAIEmptyResponseException;
use OpenAI\Laravel\Facades\OpenAI;

class InterpretWhatsappMessageWithAiService implements InterpretWhatsappMessageWithAiServiceInterface
{
    public function __invoke(string $message): string
    {
        $response = OpenAI::responses()->create([
            'model' => 'gpt-5',
            'input' => [
                [
                    'role' => 'system',
                    'content' => 'Interprete a mensagem do usuário e retorne apenas JSON com intent e filters.',
                ],
                [
                    'role' => 'user',
                    'content' => $message,
                ],
            ],
        ]);

        $content = trim((string) $response->outputText);

        if ($content === '') {
            throw new OpenAIEmptyResponseException;
        }

        return $content;
    }
}
