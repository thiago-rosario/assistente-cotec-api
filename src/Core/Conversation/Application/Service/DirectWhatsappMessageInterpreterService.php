<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Service;

use App\Core\Conversation\Application\DTO\WhatsappMessageInterpretationDTO;
use App\Core\Conversation\Application\Interfaces\Rule\SeiProcessWhatsappMessageInterpretationRuleInterface;
use App\Core\Conversation\Application\Interfaces\Rule\WhatsappMessageInterpretationRuleInterface;
use App\Core\Conversation\Application\Interfaces\Service\DirectWhatsappMessageInterpreterServiceInterface;

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
