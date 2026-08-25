<?php

declare(strict_types=1);

namespace App\Contract\Infra\Message;

use App\Contract\Application\DTO\ContractAdjustmentsOutputDTO;
use App\Contract\Application\DTO\ContractExecutionDeadlinesOutputDTO;
use App\Contract\Application\DTO\ContractValueAdditivesOutputDTO;
use App\Contract\Application\DTO\FindContractSummaryOutputDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;
use InvalidArgumentException;

class FoundContractRecordsReplyBuilder
{
    public function __construct(
        private readonly ValueAdditiveReplyBuilder $valueAdditiveReplyBuilder,
        private readonly ContractAdjustmentReplyBuilder $contractAdjustmentReplyBuilder,
        private readonly ExecutionDeadlineReplyBuilder $executionDeadlineReplyBuilder,
        private readonly ContractSummaryReplyBuilder $contractSummaryReplyBuilder,
    ) {}

    public function build(
        SearchContractInputDTO $filters,
        ContractValueAdditivesOutputDTO|ContractAdjustmentsOutputDTO|ContractExecutionDeadlinesOutputDTO|FindContractSummaryOutputDTO $result,
    ): string {
        return match (true) {
            $result instanceof ContractValueAdditivesOutputDTO => $this->valueAdditiveReplyBuilder->build($filters, $result),
            $result instanceof ContractAdjustmentsOutputDTO => $this->contractAdjustmentReplyBuilder->build($filters, $result),
            $result instanceof ContractExecutionDeadlinesOutputDTO => $this->executionDeadlineReplyBuilder->build($filters, $result),
            $result instanceof FindContractSummaryOutputDTO => $this->contractSummaryReplyBuilder->build($result),
            default => throw new InvalidArgumentException('Unsupported contract records result.'),
        };
    }
}
