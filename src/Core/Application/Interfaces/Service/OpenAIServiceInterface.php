<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Service;

/**
 * Define o contrato para serviços capazes de gerar respostas textuais via OpenAI.
 */
interface OpenAIServiceInterface
{
    /**
     * Gera uma resposta em texto a partir do prompt informado.
     *
     * @throws \RuntimeException Quando o provedor externo não retorna conteúdo utilizável.
     */
    public function generateResponse(string $prompt): string;
}
