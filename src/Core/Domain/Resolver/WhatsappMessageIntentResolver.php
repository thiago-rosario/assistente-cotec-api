<?php

declare(strict_types=1);

namespace App\Core\Domain\Resolver;

use App\Core\Application\DTO\WhatsappMessageInterpretationDTO;
use App\Core\Enum\WhatsappMessageIntentEnum;

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
