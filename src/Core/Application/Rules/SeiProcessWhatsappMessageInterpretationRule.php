<?php

declare(strict_types=1);

namespace App\Core\Application\Rules;

use App\Core\Application\DTO\WhatsappMessageInterpretationDTO;
use App\Core\Application\Interfaces\Rule\SeiProcessWhatsappMessageInterpretationRuleInterface;
use App\Core\Enum\WhatsappMessageIntentEnum;

final class SeiProcessWhatsappMessageInterpretationRule implements SeiProcessWhatsappMessageInterpretationRuleInterface
{
    private const string ProcessPattern = '/\d{3}\.\d{4,5}\.\d{4}\.\d{7}-\d{2}/';

    public function __invoke(string $message): ?WhatsappMessageInterpretationDTO
    {
        if (preg_match(self::ProcessPattern, $message, $matches) !== 1) {
            return null;
        }

        return new WhatsappMessageInterpretationDTO(
            intent: WhatsappMessageIntentEnum::SEARCH_TECHNICAL_NOTEBOOK->value,
            filters: [
                'process' => $matches[0],
            ],
        );
    }
}
