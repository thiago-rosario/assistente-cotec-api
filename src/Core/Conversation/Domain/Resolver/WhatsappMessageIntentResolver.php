<?php

declare(strict_types=1);

namespace App\Core\Conversation\Domain\Resolver;

use App\Core\Conversation\Application\DTO\WhatsappMessageInterpretationDTO;
use App\Core\Conversation\Enum\WhatsappMessageIntentEnum;

class WhatsappMessageIntentResolver
{
    public function resolve(
        WhatsappMessageInterpretationDTO $interpretation,
    ): WhatsappMessageInterpretationDTO {
        if ($this->isUnknown($interpretation) && $interpretation->filters === []) {
            return $interpretation;
        }

        return new WhatsappMessageInterpretationDTO(
            intent: WhatsappMessageIntentEnum::SEARCH_TECHNICAL_NOTEBOOK->value,
            filters: $interpretation->filters,
        );
    }

    private function isUnknown(WhatsappMessageInterpretationDTO $interpretation): bool
    {
        return $interpretation->intent === WhatsappMessageIntentEnum::UNKNOWN->value;
    }
}
