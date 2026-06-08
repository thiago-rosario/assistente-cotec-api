<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Service;

interface InterpretWhatsappMessageWithAiServiceInterface
{
    public function __invoke(string $message): string;
}
