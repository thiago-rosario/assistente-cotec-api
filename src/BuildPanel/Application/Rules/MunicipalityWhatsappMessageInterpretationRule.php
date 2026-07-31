<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\Rules;

use App\BuildPanel\Application\DTO\WhatsappMessageInterpretationDTO;
use App\BuildPanel\Application\Interfaces\Rule\WhatsappMessageInterpretationRuleInterface;
use App\BuildPanel\Application\Interfaces\Service\MunicipalityExtractorServiceInterface;
use App\BuildPanel\Enum\WhatsappMessageIntentEnum;

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
