<?php

declare(strict_types=1);

namespace App\Core\Enum;

enum WhatsappTerminalIntentEnum: string
{
    case SearchTechnicalNotebook = 'search_technical_notebook';

    case ContractValueAdditives = 'contract_value_additives';

    case ContractAdjustments = 'contract_adjustments';

    case ContractExecutionDeadlines = 'contract_execution_deadlines';

    case ContractSummary = 'contract_summary';

    /**
     * @param  array<string, mixed>  $response
     */
    public static function fromResponse(array $response): ?self
    {
        $intent = $response['intent'] ?? null;

        return is_string($intent) ? self::tryFrom($intent) : null;
    }
}
