<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\Service;

use App\BuildPanel\Application\DTO\WhatsappMessageInterpretationDTO;
use App\BuildPanel\Application\Interfaces\Rule\SeiProcessWhatsappMessageInterpretationRuleInterface;
use App\BuildPanel\Application\Interfaces\Rule\WhatsappMessageInterpretationRuleInterface;
use App\BuildPanel\Application\Interfaces\Service\DirectWhatsappMessageInterpreterServiceInterface;

class DirectWhatsappMessageInterpreterService implements DirectWhatsappMessageInterpreterServiceInterface
{
    public function __construct(
        private readonly SeiProcessWhatsappMessageInterpretationRuleInterface $seiProcessRule,
        private readonly WhatsappMessageInterpretationRuleInterface $generalRule,
    ) {}

    public function interpret(string $message): ?WhatsappMessageInterpretationDTO
    {
        $seiProcessInterpretation = ($this->seiProcessRule)($message);

        if ($seiProcessInterpretation !== null) {
            return $seiProcessInterpretation;
        }

        return $this->generalRule->interpret($message);
    }
}
