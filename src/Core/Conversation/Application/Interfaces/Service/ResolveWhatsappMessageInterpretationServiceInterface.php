<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Interfaces\Service;

use App\Core\Conversation\Application\DTO\WhatsappMessageInterpretationDTO;
use App\Core\Conversation\Enum\ConversationState;

interface ResolveWhatsappMessageInterpretationServiceInterface
{
    public function __invoke(string $message, ?ConversationState $state = null): WhatsappMessageInterpretationDTO;
}
