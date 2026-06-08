<?php

declare(strict_types=1);

namespace App\Core\Application\Rules;

use App\Core\Application\DTO\WhatsappMessageInterpretationDTO;
use App\Core\Application\Interfaces\MunicipalityExtractorServiceInterface;
use App\Core\Application\Interfaces\WhatsappMessageInterpretationRuleInterface;
use App\Core\Enum\WhatsappMessageIntentEnum;

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
