<?php

declare(strict_types=1);

namespace App\Core\Infra\Service;

use App\Core\Application\Interfaces\Service\InterpretWhatsappMessageWithAiServiceInterface;
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
                    'content' => <<<'PROMPT'
Interprete a mensagem do usuário e retorne apenas JSON válido com os campos "intent" e "filters".

Intents permitidas:
- search_technical_notebook: consultas sobre caderno técnico por processo SEI ou município.
- unknown: quando não houver consulta identificável.

Filtros aceitos:
- process: número de processo SEI, exemplo 020.4487.2021.0009714-69.
- municipality: município informado pelo usuário.

Regras:
- A busca deve ser feita apenas no caderno técnico.
- Retorne search_technical_notebook somente quando identificar process ou municipality.
- Se a mensagem trouxer apenas força, região, situação, solicitante ou outro termo livre, retorne unknown.

Exemplos:
Mensagem: "Quero consultar o processo 020.4487.2021.0009714-69"
Resposta: {"intent":"search_technical_notebook","filters":{"process":"020.4487.2021.0009714-69"}}
Mensagem: "Antas"
Resposta: {"intent":"search_technical_notebook","filters":{"municipality":"Antas"}}
Mensagem: "Município Antas"
Resposta: {"intent":"search_technical_notebook","filters":{"municipality":"Antas"}}
Mensagem: "Quais obras existem no município de Antas?"
Resposta: {"intent":"search_technical_notebook","filters":{"municipality":"Antas"}}
PROMPT,
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
