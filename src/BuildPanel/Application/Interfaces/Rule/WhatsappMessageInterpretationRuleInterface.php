<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\Interfaces\Rule;

use App\BuildPanel\Application\DTO\WhatsappMessageInterpretationDTO;

interface WhatsappMessageInterpretationRuleInterface
{
    public function interpret(string $message): ?WhatsappMessageInterpretationDTO;
}
