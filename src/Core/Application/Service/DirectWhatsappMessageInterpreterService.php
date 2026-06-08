<?php

declare(strict_types=1);

namespace App\Core\Application\Service;

use App\Core\Application\DTO\WhatsappMessageInterpretationDTO;
use App\Core\Application\Interfaces\DirectWhatsappMessageInterpreterServiceInterface;
use App\Core\Application\Interfaces\SeiProcessWhatsappMessageInterpretationRuleInterface;
use App\Core\Application\Interfaces\WhatsappMessageInterpretationRuleInterface;

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
