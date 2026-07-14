<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Interfaces\Rule;

use App\Core\Conversation\Application\DTO\WhatsappMessageInterpretationDTO;

interface SeiProcessWhatsappMessageInterpretationRuleInterface
{
    public function __invoke(string $message): ?WhatsappMessageInterpretationDTO;
}
