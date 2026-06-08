<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Rule;

use App\Core\Application\DTO\WhatsappMessageInterpretationDTO;

interface WhatsappMessageInterpretationRuleInterface
{
    public function interpret(string $message): ?WhatsappMessageInterpretationDTO;
}
