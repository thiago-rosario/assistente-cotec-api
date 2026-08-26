<?php

declare(strict_types=1);

namespace App\Contract\Application\Service;

use App\Contract\Application\DTO\ContractAdjustmentsOutputDTO;
use App\Contract\Application\DTO\ContractExecutionDeadlinesOutputDTO;
use App\Contract\Application\DTO\ContractValueAdditivesOutputDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;
use App\Contract\Application\Interfaces\Resolver\ContractSearchTypeResolverInterface;
use App\Contract\Application\Interfaces\Service\ContractWhatsappMessageServiceInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractAdjustmentsUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractExecutionDeadlineUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractSummaryUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractValueAdditivesUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\SearchContractUsecaseInterface;
use App\Contract\Enum\ContractSearchTypeEnum;
use App\Contract\Infra\Message\FoundContractRecordsReplyBuilder;
use App\Contract\Infra\Message\WhatsappContractDefaultReplies;
use App\Contract\Infra\Message\WhatsappContractResponsePayloadFactory;

class ContractWhatsappMessageService implements ContractWhatsappMessageServiceInterface
{
    private const int ValueAdditivesOption = 1;

    private const int AdjustmentsOption = 2;

    private const int ExecutionDeadlinesOption = 3;

    private const int SummaryOption = 4;

    public function __construct(
        private readonly FindContractValueAdditivesUsecaseInterface $valueAdditives,
        private readonly FindContractAdjustmentsUsecaseInterface $adjustments,
        private readonly FindContractExecutionDeadlineUsecaseInterface $executionDeadlines,
        private readonly FindContractSummaryUsecaseInterface $summary,
        private readonly SearchContractUsecaseInterface $searchContracts,
        private readonly ContractSearchTypeResolverInterface $searchTypeResolver,
        private readonly WhatsappContractDefaultReplies $defaultReplies,
        private readonly WhatsappContractResponsePayloadFactory $payloadFactory,
        private readonly FoundContractRecordsReplyBuilder $foundRecordsReplyBuilder,
    ) {}

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    public function menu(): array
    {
        return $this->payloadFactory->empty('contract_menu', $this->defaultReplies->menu());
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    public function searchPrompt(int $option): array
    {
        $reply = match ($option) {
            self::ValueAdditivesOption => $this->defaultReplies->valueAdditiveSearch(),
            self::AdjustmentsOption => $this->defaultReplies->contractAdjustmentSearch(),
            self::ExecutionDeadlinesOption => $this->defaultReplies->executionDeadlineSearch(),
            self::SummaryOption => $this->defaultReplies->contractSummarySearch(),
            default => $this->defaultReplies->invalidMenuOption(),
        };

        $intent = in_array($option, [1, 2, 3, 4], true)
            ? 'contract_search_prompt'
            : 'contract_invalid_menu_option';

        return $this->payloadFactory->empty($intent, $reply);
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<mixed>, filters: array<string, mixed>}
     */
    public function search(int $option, string $searchTerm): array
    {
        $searchType = $this->searchTypeResolver->resolve($searchTerm);

        if ($searchType === null || ! $this->supports($option, $searchType)) {
            return $this->payloadFactory->empty('contract_unknown', $this->defaultReplies->unknownIntent());
        }

        $filters = new SearchContractInputDTO(
            searchTerm: trim($searchTerm),
            searchType: $searchType,
        );
        $result = match ($option) {
            self::ValueAdditivesOption => $this->findValueAdditives($filters),
            self::AdjustmentsOption => $this->findAdjustments($filters),
            self::ExecutionDeadlinesOption => $this->findExecutionDeadlines($filters),
            self::SummaryOption => ($this->summary)($filters),
            default => null,
        };

        if ($result === null) {
            return $this->payloadFactory->empty('contract_unknown', $this->defaultReplies->unknownIntent());
        }

        if ($result->total === 0) {
            return $this->payloadFactory->empty(
                $this->intent($option),
                $this->defaultReplies->noRecords(),
                [
                    'searchTerm' => $filters->searchTerm,
                    'searchType' => $filters->searchType->value,
                ],
            );
        }

        return $this->payloadFactory->withRecords(
            intent: $this->intent($option),
            filters: $filters,
            result: $result,
            reply: $this->foundRecordsReplyBuilder->build($filters, $result),
        );
    }

    private function supports(int $option, ContractSearchTypeEnum $searchType): bool
    {
        if ($option === self::SummaryOption) {
            return in_array($searchType, [
                ContractSearchTypeEnum::Municipality,
                ContractSearchTypeEnum::ContractNumber,
            ], true);
        }

        return in_array($searchType, [
            ContractSearchTypeEnum::Municipality,
            ContractSearchTypeEnum::Company,
            ContractSearchTypeEnum::ContractNumber,
        ], true);
    }

    private function findValueAdditives(SearchContractInputDTO $filters): ContractValueAdditivesOutputDTO
    {
        if ($filters->searchType !== ContractSearchTypeEnum::Company) {
            return ($this->valueAdditives)($filters);
        }

        $data = [];

        foreach ($this->contractNumbersForCompany($filters) as $contractNumber) {
            $data = [
                ...$data,
                ...($this->valueAdditives)(new SearchContractInputDTO(
                    searchTerm: $contractNumber,
                    searchType: ContractSearchTypeEnum::ContractNumber,
                ))->data,
            ];
        }

        return new ContractValueAdditivesOutputDTO(
            searchTerm: $filters->searchTerm,
            searchType: $filters->searchType,
            total: count($data),
            data: $data,
        );
    }

    private function findAdjustments(SearchContractInputDTO $filters): ContractAdjustmentsOutputDTO
    {
        if ($filters->searchType !== ContractSearchTypeEnum::Company) {
            return ($this->adjustments)($filters);
        }

        $data = [];
        $total = 0;

        foreach ($this->contractNumbersForCompany($filters) as $contractNumber) {
            $result = ($this->adjustments)(new SearchContractInputDTO(
                searchTerm: $contractNumber,
                searchType: ContractSearchTypeEnum::ContractNumber,
            ));
            $total += $result->total;
            $data = [...$data, ...$result->data];
        }

        return new ContractAdjustmentsOutputDTO(
            searchTerm: $filters->searchTerm,
            searchType: $filters->searchType,
            total: $total,
            data: $data,
        );
    }

    private function findExecutionDeadlines(SearchContractInputDTO $filters): ContractExecutionDeadlinesOutputDTO
    {
        if ($filters->searchType !== ContractSearchTypeEnum::Company) {
            return ($this->executionDeadlines)($filters);
        }

        $data = [];

        foreach ($this->contractNumbersForCompany($filters) as $contractNumber) {
            $data = [
                ...$data,
                ...($this->executionDeadlines)(new SearchContractInputDTO(
                    searchTerm: $contractNumber,
                    searchType: ContractSearchTypeEnum::ContractNumber,
                ))->data,
            ];
        }

        return new ContractExecutionDeadlinesOutputDTO(
            searchTerm: $filters->searchTerm,
            searchType: $filters->searchType,
            total: count($data),
            data: $data,
        );
    }

    /**
     * @return list<string>
     */
    private function contractNumbersForCompany(SearchContractInputDTO $filters): array
    {
        return array_map(
            static fn (object $contract): string => $contract->contractNumber,
            ($this->searchContracts)($filters)->data,
        );
    }

    private function intent(int $option): string
    {
        return match ($option) {
            self::ValueAdditivesOption => 'contract_value_additives',
            self::AdjustmentsOption => 'contract_adjustments',
            self::ExecutionDeadlinesOption => 'contract_execution_deadlines',
            self::SummaryOption => 'contract_summary',
            default => 'contract_unknown',
        };
    }
}
