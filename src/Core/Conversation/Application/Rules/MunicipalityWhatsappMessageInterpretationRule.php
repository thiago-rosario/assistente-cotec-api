<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Rules;

use App\Core\Conversation\Application\DTO\WhatsappMessageInterpretationDTO;
use App\Core\Conversation\Application\Interfaces\Rule\WhatsappMessageInterpretationRuleInterface;
use App\Core\Conversation\Application\Interfaces\Service\MunicipalityExtractorServiceInterface;
use App\Core\Conversation\Enum\WhatsappMessageIntentEnum;

class MunicipalityWhatsappMessageInterpretationRule implements WhatsappMessageInterpretationRuleInterface
{
    public function __construct(
        private readonly MunicipalityExtractorServiceInterface $service,
    ) {}

    public function interpret(string $message): ?WhatsappMessageInterpretationDTO
    {
        $municipality = $this->service->extract($message);

        if (! $municipality) {
            return null;
        }

        return new WhatsappMessageInterpretationDTO(
            intent: WhatsappMessageIntentEnum::SEARCH_TECHNICAL_NOTEBOOK->value,
            filters: [
                'municipality' => $municipality,
            ],
        );
    }
}
