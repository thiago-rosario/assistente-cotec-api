<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\Interfaces\Rule;

use App\BuildPanel\Application\DTO\WhatsappMessageInterpretationDTO;

interface SeiProcessWhatsappMessageInterpretationRuleInterface
{
    public function __invoke(string $message): ?WhatsappMessageInterpretationDTO;
}
