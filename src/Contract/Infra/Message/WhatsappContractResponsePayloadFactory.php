<?php

declare(strict_types=1);

namespace App\Contract\Infra\Message;

use App\Contract\Application\DTO\ContractAdjustmentsOutputDTO;
use App\Contract\Application\DTO\ContractExecutionDeadlinesOutputDTO;
use App\Contract\Application\DTO\ContractValueAdditivesOutputDTO;
use App\Contract\Application\DTO\FindContractSummaryOutputDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;

class WhatsappContractResponsePayloadFactory
{
    /**
     * @return array{reply: string, intent: string, total: int, data: list<object>, filters: array<string, mixed>}
     */
    public function empty(string $intent, string $reply): array
    {
        return [
            'reply' => $reply,
            'intent' => $intent,
            'total' => 0,
            'data' => [],
            'filters' => [],
        ];
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<object>, filters: array<string, mixed>}
     */
    public function withRecords(
        string $intent,
        SearchContractInputDTO $filters,
        ContractValueAdditivesOutputDTO|ContractAdjustmentsOutputDTO|ContractExecutionDeadlinesOutputDTO|FindContractSummaryOutputDTO $result,
        string $reply,
    ): array {
        return [
            'reply' => $reply,
            'intent' => $intent,
            'total' => $result->total,
            'data' => $result->data,
            'filters' => [
                'searchTerm' => $filters->searchTerm,
                'searchType' => $filters->searchType->value,
            ],
        ];
    }
}
