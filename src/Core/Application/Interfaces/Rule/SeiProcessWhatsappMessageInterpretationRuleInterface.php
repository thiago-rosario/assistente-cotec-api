<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Rule;

use App\Core\Application\DTO\WhatsappMessageInterpretationDTO;

interface SeiProcessWhatsappMessageInterpretationRuleInterface
{
    public function __invoke(string $message): ?WhatsappMessageInterpretationDTO;
}
