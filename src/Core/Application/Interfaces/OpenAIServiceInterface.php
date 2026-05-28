<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces;

interface OpenAIServiceInterface
{
    public function generateResponse(string $prompt): string;
}
