<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Interfaces\Rule;

use App\Core\Conversation\Application\DTO\WhatsappMessageInterpretationDTO;

interface WhatsappMessageInterpretationRuleInterface
{
    public function interpret(string $message): ?WhatsappMessageInterpretationDTO;
}
