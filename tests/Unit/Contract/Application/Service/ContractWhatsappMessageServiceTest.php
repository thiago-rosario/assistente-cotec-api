<?php

use App\Contract\Application\DTO\ContractSummaryOutputDTO;
use App\Contract\Application\DTO\FindContractSummaryOutputDTO;
use App\Contract\Application\Interfaces\Usecase\FindContractAdjustmentsUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractExecutionDeadlineUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractSummaryUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractValueAdditivesUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\SearchContractUsecaseInterface;
use App\Contract\Application\Resolver\ContractSearchTypeResolver;
use App\Contract\Application\Service\ContractWhatsappMessageService;
use App\Contract\Enum\ContractSearchTypeEnum;
use App\Contract\Infra\Message\ContractAdjustmentReplyBuilder;
use App\Contract\Infra\Message\ContractSummaryReplyBuilder;
use App\Contract\Infra\Message\ExecutionDeadlineReplyBuilder;
use App\Contract\Infra\Message\FoundContractRecordsReplyBuilder;
use App\Contract\Infra\Message\ValueAdditiveReplyBuilder;
use App\Contract\Infra\Message\WhatsappContractDefaultReplies;
use App\Contract\Infra\Message\WhatsappContractRecordValueFormatter;
use App\Contract\Infra\Message\WhatsappContractResponsePayloadFactory;

it('returns the contract menu and summary prompt through the existing replies', function () {
    $service = contractWhatsappMessageService();

    expect($service->menu()['reply'])
        ->toContain('Acompanhamento de Contratos')
        ->and($service->searchPrompt(4)['reply'])
        ->toContain('RESUMO DO ACOMPANHAMENTO CONTRATUAL');
});

it('executes the municipality contract summary and formats its existing result', function () {
    $summaryUsecase = Mockery::mock(FindContractSummaryUsecaseInterface::class);
    $summaryUsecase->shouldReceive('__invoke')
        ->once()
        ->with(Mockery::on(
            fn ($input): bool => $input->searchTerm === 'Ibotirama'
                && $input->searchType === ContractSearchTypeEnum::Municipality,
        ))
        ->andReturn(new FindContractSummaryOutputDTO(
            searchTerm: 'Ibotirama',
            searchType: ContractSearchTypeEnum::Municipality,
            total: 1,
            data: [new ContractSummaryOutputDTO(
                contractNumber: '08/2023',
                company: 'Empresa X',
                seiProcess: '020.1234.2026.0000001-10',
                municipalities: ['Ibotirama'],
            )],
        ));

    $service = contractWhatsappMessageService(summaryUsecase: $summaryUsecase);
    $result = $service->search(4, 'Ibotirama');

    expect($result['intent'])->toBe('contract_summary')
        ->and($result['total'])->toBe(1)
        ->and($result['filters'])->toBe([
            'searchTerm' => 'Ibotirama',
            'searchType' => 'municipality',
        ])
        ->and($result['reply'])->toContain('RESUMO DO CONTRATO 08/2023');
});

it('classifies the supported contract search values', function () {
    $resolver = new ContractSearchTypeResolver;

    expect($resolver->resolve('Ibotirama'))->toBe(ContractSearchTypeEnum::Municipality)
        ->and($resolver->resolve('UFC ENGENHARIA'))->toBe(ContractSearchTypeEnum::Company)
        ->and($resolver->resolve('148/2024'))->toBe(ContractSearchTypeEnum::ContractNumber)
        ->and($resolver->resolve('020.4487.2021.0009714-69'))->toBeNull();
});

function contractWhatsappMessageService(
    ?FindContractSummaryUsecaseInterface $summaryUsecase = null,
): ContractWhatsappMessageService {
    $valueFormatter = new WhatsappContractRecordValueFormatter;

    return new ContractWhatsappMessageService(
        valueAdditives: Mockery::mock(FindContractValueAdditivesUsecaseInterface::class),
        adjustments: Mockery::mock(FindContractAdjustmentsUsecaseInterface::class),
        executionDeadlines: Mockery::mock(FindContractExecutionDeadlineUsecaseInterface::class),
        summary: $summaryUsecase ?? Mockery::mock(FindContractSummaryUsecaseInterface::class),
        searchContracts: Mockery::mock(SearchContractUsecaseInterface::class),
        searchTypeResolver: new ContractSearchTypeResolver,
        defaultReplies: new WhatsappContractDefaultReplies,
        payloadFactory: new WhatsappContractResponsePayloadFactory,
        foundRecordsReplyBuilder: new FoundContractRecordsReplyBuilder(
            valueAdditiveReplyBuilder: new ValueAdditiveReplyBuilder($valueFormatter),
            contractAdjustmentReplyBuilder: new ContractAdjustmentReplyBuilder($valueFormatter),
            executionDeadlineReplyBuilder: new ExecutionDeadlineReplyBuilder($valueFormatter),
            contractSummaryReplyBuilder: new ContractSummaryReplyBuilder($valueFormatter),
        ),
    );
}
