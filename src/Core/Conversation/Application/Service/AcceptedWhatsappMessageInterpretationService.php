<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Service;

use App\Core\Conversation\Application\Interfaces\Service\AcceptedWhatsappMessageInterpretationServiceInterface;
use App\Core\Conversation\Enum\WhatsappMessageIntentEnum;

class AcceptedWhatsappMessageInterpretationService implements AcceptedWhatsappMessageInterpretationServiceInterface
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function accepts(string $intent, array $filters): bool
    {
        if ($intent !== WhatsappMessageIntentEnum::SEARCH_TECHNICAL_NOTEBOOK->value) {
            return false;
        }

        return filled($filters['process'] ?? null) || filled($filters['municipality'] ?? null);
    }
}
