<?php

use App\Contract\Application\DTO\ContractExtractDTO;
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
        ->toContain('EXTRATO DO ACOMPANHAMENTO CONTRATUAL');
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
            data: [new ContractExtractDTO(
                contractNumber: '08/2023',
                company: 'Empresa X',
                municipality: 'Ibotirama',
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
        ->and($result['reply'])->toContain('EXTRATO CONTRATUAL — 08/2023')
        ->and($result['reply'])->toContain('➕ Aditivos: Sem registros')
        ->and($result['reply'])->toContain('📅 Prazos de execução: Sem registros')
        ->and($result['reply'])->not->toContain('Não informado');
});

it('formats the general contract result as a compact extract', function () {
    $builder = new ContractSummaryReplyBuilder(new WhatsappContractRecordValueFormatter);
    $reply = $builder->build(new FindContractSummaryOutputDTO(
        searchTerm: 'Salvador',
        searchType: ContractSearchTypeEnum::Municipality,
        total: 1,
        data: [new ContractExtractDTO(
            contractNumber: '13/2024',
            company: 'WIA Engenharia e Consultoria Ambiental Eireli',
            municipality: 'Salvador',
            seiProcess: '020.18069.2024.0024827-27',
            currentSituation: 'Em acompanhamento',
            updatedValue: 5690942.11,
            additivesCount: 3,
            additivesStatus: '3 registros publicados',
            readjustmentsCount: 2,
            readjustmentsStatus: '1 publicado e liquidado; 1 em tramitação',
            executionDeadlinesStatus: 'Sem registros',
            lastMovementDate: new DateTimeImmutable('2026-05-15'),
            currentPending: 'Reajuste em tramitação na SSP/GAB/DG',
        )],
    ));

    expect($reply)
        ->toContain('📋 EXTRATO CONTRATUAL — 13/2024')
        ->toContain('🏢 Empresa: WIA Engenharia e Consultoria Ambiental Eireli')
        ->toContain('📄 Processo principal: 020.18069.2024.0024827-27')
        ->toContain('💰 Valor atualizado: R$ 5.690.942,11')
        ->toContain('➕ Aditivos: 3 registros publicados')
        ->toContain('📊 Reajustes e reequilíbrios: 2 registros')
        ->toContain('  • 1 publicado e liquidado')
        ->toContain('  • 1 em tramitação')
        ->toContain('📅 Prazos de execução: Sem registros')
        ->toContain('🔄 Última movimentação: 15/05/2026')
        ->toContain('⚠️ Pendência atual: Reajuste em tramitação na SSP/GAB/DG')
        ->not->toContain('Registro 1 de')
        ->not->toContain('ADITIVOS DE VALOR')
        ->not->toContain('Não informado');
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
