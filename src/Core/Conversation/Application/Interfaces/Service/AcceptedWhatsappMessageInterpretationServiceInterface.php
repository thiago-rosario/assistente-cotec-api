<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Interfaces\Service;

interface AcceptedWhatsappMessageInterpretationServiceInterface
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function accepts(string $intent, array $filters): bool;
}
