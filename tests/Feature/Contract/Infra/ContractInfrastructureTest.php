<?php

use App\Contract\Application\Assembly\ContractSummaryAssembler;
use App\Contract\Application\DTO\SearchContractInputDTO;
use App\Contract\Application\Interfaces\Adapter\ContractSheetAdapterInterface;
use App\Contract\Application\Interfaces\Assembly\ContractSummaryAssemblerInterface;
use App\Contract\Application\Interfaces\Mapper\ContractExecutionDeadlineSheetMapperInterface;
use App\Contract\Application\Interfaces\Mapper\ContractReadjustmentSheetMapperInterface;
use App\Contract\Application\Interfaces\Mapper\ContractSheetMapperInterface;
use App\Contract\Application\Interfaces\Mapper\ValueAdditiveSheetMapperInterface;
use App\Contract\Application\Interfaces\Parser\ContractDateParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractIntegerParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractMoneyParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractNullableStringParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractNumberParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractRequiredStringParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractSearchValueParserInterface;
use App\Contract\Application\Interfaces\Resolver\MunicipalityContractResolverInterface;
use App\Contract\Application\Interfaces\Service\ContractRemainingDaysCalculatorServiceInterface;
use App\Contract\Application\Interfaces\Service\ContractWhatsappMessageServiceInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractAdjustmentsUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractExecutionDeadlineUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractSummaryUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\FindContractValueAdditivesUsecaseInterface;
use App\Contract\Application\Interfaces\Usecase\SearchContractUsecaseInterface;
use App\Contract\Application\Resolver\MunicipalityContractResolver;
use App\Contract\Application\Service\ContractRemainingDaysCalculatorService;
use App\Contract\Application\Usecase\FindContractAdjustmentsUsecase;
use App\Contract\Application\Usecase\FindContractExecutionDeadlineUsecase;
use App\Contract\Application\Usecase\FindContractSummaryUsecase;
use App\Contract\Application\Usecase\FindContractValueAdditivesUsecase;
use App\Contract\Application\Usecase\SearchContractUsecase;
use App\Contract\Domain\Entity\ContractEntity;
use App\Contract\Domain\Entity\ContractExecutionDeadlineEntity;
use App\Contract\Domain\Entity\ContractReadjustmentEntity;
use App\Contract\Domain\Entity\ValueAdditiveEntity;
use App\Contract\Domain\Repository\ContractExecutionDeadlineRepositoryInterface;
use App\Contract\Domain\Repository\ContractReadjustmentRepositoryInterface;
use App\Contract\Domain\Repository\ContractRepositoryInterface;
use App\Contract\Domain\Repository\ValueAdditiveRepositoryInterface;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;
use App\Contract\Enum\ContractSearchTypeEnum;
use App\Contract\Infra\Adapter\ContractSheetAdapter;
use App\Contract\Infra\Exception\ContractSheetRowMappingException;
use App\Contract\Infra\Mapper\ContractExecutionDeadlineSheetMapper;
use App\Contract\Infra\Mapper\ContractReadjustmentSheetMapper;
use App\Contract\Infra\Mapper\ContractSheetMapper;
use App\Contract\Infra\Mapper\ValueAdditiveSheetMapper;
use App\Contract\Infra\Parser\ContractDateParser;
use App\Contract\Infra\Parser\ContractIntegerParser;
use App\Contract\Infra\Parser\ContractMoneyParser;
use App\Contract\Infra\Parser\ContractNullableStringParser;
use App\Contract\Infra\Parser\ContractNumberParser;
use App\Contract\Infra\Parser\ContractRequiredStringParser;
use App\Contract\Infra\Parser\ContractSearchValueParser;
use App\Contract\Infra\Repository\Gateway\ContractGoogleSheetGatewayRepository;
use App\Contract\Infra\Repository\Gateway\ValueAdditiveGoogleSheetGatewayRepository;
use App\Contract\Infra\Repository\SheetRepository\ContractExecutionDeadlineGoogleSheetRepository;
use App\Contract\Infra\Repository\SheetRepository\ContractReadjustmentGoogleSheetRepository;
use App\Core\Exception\GoogleSheetReadException;
use Illuminate\Support\Collection;
use Revolution\Google\Sheets\Facades\Sheets;

it('registers the contract application and repository bindings', function () {
    expect(app(ContractSummaryAssemblerInterface::class))->toBeInstanceOf(
        ContractSummaryAssembler::class,
    )
        ->and(app(MunicipalityContractResolverInterface::class))->toBeInstanceOf(
            MunicipalityContractResolver::class,
        )
        ->and(app(ContractRemainingDaysCalculatorServiceInterface::class))->toBeInstanceOf(
            ContractRemainingDaysCalculatorService::class,
        )
        ->and(app(FindContractAdjustmentsUsecaseInterface::class))->toBeInstanceOf(FindContractAdjustmentsUsecase::class)
        ->and(app(FindContractExecutionDeadlineUsecaseInterface::class))->toBeInstanceOf(FindContractExecutionDeadlineUsecase::class)
        ->and(app(FindContractSummaryUsecaseInterface::class))->toBeInstanceOf(FindContractSummaryUsecase::class)
        ->and(app(FindContractValueAdditivesUsecaseInterface::class))->toBeInstanceOf(FindContractValueAdditivesUsecase::class)
        ->and(app(SearchContractUsecaseInterface::class))->toBeInstanceOf(SearchContractUsecase::class)
        ->and(app(ContractRepositoryInterface::class))->toBeInstanceOf(ContractGoogleSheetGatewayRepository::class)
        ->and(app(ValueAdditiveRepositoryInterface::class))->toBeInstanceOf(ValueAdditiveGoogleSheetGatewayRepository::class)
        ->and(app(ContractReadjustmentRepositoryInterface::class))->toBeInstanceOf(ContractReadjustmentGoogleSheetRepository::class)
        ->and(app(ContractExecutionDeadlineRepositoryInterface::class))->toBeInstanceOf(ContractExecutionDeadlineGoogleSheetRepository::class);
});

it('resolves the contract sheet adapter through its interface', function () {
    expect(app(ContractSheetAdapterInterface::class))->toBeInstanceOf(ContractSheetAdapter::class);
});

it('resolves contract parser and mapper bindings from the module provider', function (
    string $abstract,
    string $concrete,
) {
    expect(app($abstract))->toBeInstanceOf($concrete);
})->with([
    [ContractDateParserInterface::class, ContractDateParser::class],
    [ContractIntegerParserInterface::class, ContractIntegerParser::class],
    [ContractMoneyParserInterface::class, ContractMoneyParser::class],
    [ContractNullableStringParserInterface::class, ContractNullableStringParser::class],
    [ContractNumberParserInterface::class, ContractNumberParser::class],
    [ContractRequiredStringParserInterface::class, ContractRequiredStringParser::class],
    [ContractSearchValueParserInterface::class, ContractSearchValueParser::class],
    [ContractExecutionDeadlineSheetMapperInterface::class, ContractExecutionDeadlineSheetMapper::class],
    [ContractReadjustmentSheetMapperInterface::class, ContractReadjustmentSheetMapper::class],
    [ContractSheetMapperInterface::class, ContractSheetMapper::class],
    [ValueAdditiveSheetMapperInterface::class, ValueAdditiveSheetMapper::class],
]);

it('maps the contract register row without dropping contract fields', function () {
    $contract = app(ContractSheetMapperInterface::class)->map([
        'Nº DO CT' => '08 / 2023',
        'EMPRESA' => 'Empresa X',
        'PROCESSO SEI Nº' => '020.1234.2026.0000001-10',
        'MUNICÍPIO' => 'Feira de Santana',
        'OBJETO' => 'Construção de unidade pública',
        'VALOR INICIAL' => 'R$ 1.440.034,92',
        'VALOR ATUALIZADO' => 'R$ 1.500.000,00',
        'VIGÊNCIA INÍCIO' => '01/02/2026',
        'VIGÊNCIA FINAL' => '31/01/2027',
        'EXECUÇÃO FINAL' => '-',
        'SITUAÇÃO' => 'PUBLICADO',
    ]);

    expect($contract)->toBeInstanceOf(ContractEntity::class)
        ->and($contract->contractNumber)->toBe('08/2023')
        ->and($contract->municipalities)->toBe(['Feira de Santana'])
        ->and($contract->initialValue)->toBe(1440034.92)
        ->and($contract->updatedValue)->toBe(1500000.0)
        ->and($contract->validityStartDate)->toEqual(new DateTimeImmutable('2026-02-01'))
        ->and($contract->validityEndDate)->toEqual(new DateTimeImmutable('2027-01-31'))
        ->and($contract->executionDeadline)->toBeNull();
});

it('maps every value additive field, including brazilian money and optional dashes', function () {
    $additive = app(ValueAdditiveSheetMapperInterface::class)->map(array_combine(
        valueAdditiveInfrastructureHeader(),
        valueAdditiveInfrastructureRow(
            value: '-R$ 25.772,98',
            publishedValue: '-',
            observation: '-',
        ),
    ));

    expect($additive)->toBeInstanceOf(ValueAdditiveEntity::class)
        ->and($additive->contractNumber)->toBe('08/2023')
        ->and($additive->municipality)->toBe('FEIRA DE SANTANA')
        ->and($additive->value)->toBe(-25772.98)
        ->and($additive->publishedValue)->toBeNull()
        ->and($additive->entryDate)->toBe('2026-01-10')
        ->and($additive->publicationDate)->toBe('2026-02-10')
        ->and($additive->processingTimeDays)->toBeNull()
        ->and($additive->observation)->toBeNull();
});

it('accepts a trailing separator in brazilian money values from the sheet', function () {
    expect(app(ContractMoneyParserInterface::class)->parse('R$ 416.858,80,'))
        ->toBe(416858.8);
});

it('treats completed execution text as an empty date', function () {
    expect(app(ContractDateParserInterface::class)->parse('SEM EXECUÇÃO/OBRA JÁ CONCLUÍDA'))
        ->toBeNull();
});

it('maps all readjustment and deadline fields with typed dates', function () {
    $readjustment = app(ContractReadjustmentSheetMapperInterface::class)->map(array_combine(
        readjustmentInfrastructureHeader(),
        readjustmentInfrastructureRow(),
    ));
    $deadline = app(ContractExecutionDeadlineSheetMapperInterface::class)->map(array_combine(
        executionDeadlineInfrastructureHeader(),
        executionDeadlineInfrastructureRow(),
    ));

    expect($readjustment)->toBeInstanceOf(ContractReadjustmentEntity::class)
        ->and($readjustment->contractNumber)->toBe('08/2023')
        ->and($readjustment->contemplatedValue)->toBe(5000.0)
        ->and($readjustment->entryDate)->toEqual(new DateTimeImmutable('2026-01-10'))
        ->and($readjustment->publicationDate)->toBeNull()
        ->and($readjustment->paymentSei)->toBeNull()
        ->and($deadline)->toBeInstanceOf(ContractExecutionDeadlineEntity::class)
        ->and($deadline->contractNumber)->toBe('08/2023')
        ->and($deadline->municipality)->toBeNull()
        ->and($deadline->validityEndDate)->toEqual(new DateTimeImmutable('2027-01-31'))
        ->and($deadline->executionEndDate)->toEqual(new DateTimeImmutable('2026-12-31'))
        ->and($deadline->publicationTimeDays)->toBeNull();
});

it('finds all value additives by municipality and preserves repeated contract records', function () {
    mockContractInfrastructureSheet('value-additives', [
        valueAdditiveInfrastructureHeader(),
        valueAdditiveInfrastructureRow(additiveNumber: '1'),
        valueAdditiveInfrastructureRow(additiveNumber: '2'),
        valueAdditiveInfrastructureRow(contractNumber: '47/2025', municipality: 'FEIRA DE SANTANA', additiveNumber: '1'),
    ]);

    $records = app(ValueAdditiveRepositoryInterface::class)->findByMunicipality(
        new MunicipalityValueObject('feira de santana'),
    );

    expect($records)->toHaveCount(3)
        ->and($records[0]->additiveNumber)->toBe('1')
        ->and($records[1]->additiveNumber)->toBe('2')
        ->and($records[2]->contractNumber)->toBe('47/2025');
});

it('finds all value additives by contract number and by the combined filters', function () {
    mockContractInfrastructureSheet('value-additives', [
        valueAdditiveInfrastructureHeader(),
        valueAdditiveInfrastructureRow(additiveNumber: '1'),
        valueAdditiveInfrastructureRow(additiveNumber: '2'),
        valueAdditiveInfrastructureRow(municipality: 'Salvador', additiveNumber: '3'),
    ], times: 2);

    $repository = app(ValueAdditiveRepositoryInterface::class);
    $contractNumber = new ContractNumberValueObject('08 / 2023');

    expect($repository->findByContractNumber($contractNumber))->toHaveCount(3)
        ->and($repository->findByMunicipalityAndContractNumber(
            new MunicipalityValueObject('FEIRA DE SANTANA'),
            $contractNumber,
        ))->toHaveCount(2);
});

it('finds contracts by number, company and municipality using their requested source rows', function () {
    mockContractInfrastructureSheet('contracts', [
        contractInfrastructureHeader(),
        contractInfrastructureRow(contractNumber: '08/2023', company: 'Empresa X'),
        contractInfrastructureRow(contractNumber: '47/2025', company: 'Empresa X'),
    ], times: 2);
    mockContractInfrastructureSheet('value-additives', [
        valueAdditiveInfrastructureHeader(),
        valueAdditiveInfrastructureRow(additiveNumber: '1'),
        valueAdditiveInfrastructureRow(contractNumber: '47/2025', municipality: 'FEIRA DE SANTANA', additiveNumber: '2'),
    ]);
    $repository = app(ContractRepositoryInterface::class);

    expect($repository->findByContractNumber(new ContractNumberValueObject('08 / 2023'))?->company)
        ->toBe('Empresa X')
        ->and($repository->findByCompany('empresa x'))->toHaveCount(2)
        ->and($repository->findByMunicipality(new MunicipalityValueObject('feira de santana')))->toHaveCount(2);
});

it('finds a contract by its SEI process', function () {
    mockContractInfrastructureSheet('contracts', [
        contractInfrastructureHeader(),
        contractInfrastructureRow(),
    ]);

    $contract = app(ContractRepositoryInterface::class)->findBySeiProcess(
        '020.1234.2026.0000001-10',
    );

    expect($contract)->toBeInstanceOf(ContractEntity::class)
        ->and($contract->contractNumber)->toBe('08/2023');
});

it('builds summaries by municipality for every related contract', function () {
    mockContractInfrastructureSequence([
        [
            valueAdditiveInfrastructureHeader(),
            valueAdditiveInfrastructureRow(contractNumber: '08/2023', additiveNumber: '1'),
            valueAdditiveInfrastructureRow(contractNumber: '47/2025', additiveNumber: '2'),
        ],
        [
            contractInfrastructureHeader(),
            contractInfrastructureRow(contractNumber: '08/2023'),
            contractInfrastructureRow(contractNumber: '47/2025'),
        ],
        [
            valueAdditiveInfrastructureHeader(),
            valueAdditiveInfrastructureRow(contractNumber: '08/2023', additiveNumber: '1'),
        ],
        [
            readjustmentInfrastructureHeader(),
            readjustmentInfrastructureRow(),
        ],
        [
            executionDeadlineInfrastructureHeader(),
            executionDeadlineInfrastructureRow(),
        ],
        [
            contractInfrastructureHeader(),
            contractInfrastructureRow(contractNumber: '08/2023'),
            contractInfrastructureRow(contractNumber: '47/2025'),
        ],
        [
            valueAdditiveInfrastructureHeader(),
            valueAdditiveInfrastructureRow(contractNumber: '47/2025', additiveNumber: '2'),
        ],
        [
            readjustmentInfrastructureHeader(),
            readjustmentInfrastructureRow(),
        ],
        [
            executionDeadlineInfrastructureHeader(),
            executionDeadlineInfrastructureRow(),
        ],
    ]);

    $result = app(ContractWhatsappMessageServiceInterface::class)->search(4, 'FEIRA DE SANTANA');

    expect($result['intent'])->toBe('contract_summary')
        ->and($result['total'])->toBe(2)
        ->and($result['data'])->toHaveCount(2)
        ->and($result['data'][0]->additivesCount)->toBe(1)
        ->and($result['data'][0]->readjustmentsCount)->toBe(1)
        ->and($result['data'][0]->executionDeadlinesStatus)->toBe('1 registro em execução')
        ->and($result['data'][1]->contractNumber)->toBe('47/2025')
        ->and($result['reply'])->toContain('📋 EXTRATO CONTRATUAL — 08/2023')
        ->and($result['reply'])->toContain('📋 EXTRATO CONTRATUAL — 47/2025')
        ->and($result['reply'])->not->toContain('ADITIVOS DE VALOR')
        ->and($result['reply'])->not->toContain('Registro 1 de');
});

it('builds a compact summary directly by contract number without detail lists', function () {
    mockContractInfrastructureSheet('contracts', [
        contractInfrastructureHeader(),
        contractInfrastructureRow(),
    ]);
    mockContractInfrastructureSheet('value-additives', [
        valueAdditiveInfrastructureHeader(),
        valueAdditiveInfrastructureRow(additiveNumber: '1'),
        valueAdditiveInfrastructureRow(additiveNumber: '2'),
    ]);
    mockContractInfrastructureSheet('readjustments', [
        readjustmentInfrastructureHeader(),
        readjustmentInfrastructureRow(apostilleNumber: '1'),
        readjustmentInfrastructureRow(apostilleNumber: '2'),
    ]);
    mockContractInfrastructureSheet('execution-deadlines', [
        executionDeadlineInfrastructureHeader(),
        executionDeadlineInfrastructureRow(observation: '1'),
        executionDeadlineInfrastructureRow(observation: '2'),
    ]);

    $result = app(FindContractSummaryUsecaseInterface::class)(new SearchContractInputDTO(
        searchTerm: '08/2023',
        searchType: ContractSearchTypeEnum::ContractNumber,
    ));

    expect($result->total)->toBe(1)
        ->and($result->data[0]->additivesCount)->toBe(2)
        ->and($result->data[0]->readjustmentsCount)->toBe(2)
        ->and($result->data[0]->executionDeadlinesStatus)->toBe('2 registros em execução');
});

it('finds every readjustment and execution deadline for a contract number', function () {
    mockContractInfrastructureSheet('readjustments', [
        readjustmentInfrastructureHeader(),
        readjustmentInfrastructureRow(apostilleNumber: '1'),
        readjustmentInfrastructureRow(apostilleNumber: '2'),
    ]);
    mockContractInfrastructureSheet('execution-deadlines', [
        executionDeadlineInfrastructureHeader(),
        executionDeadlineInfrastructureRow(observation: 'Registro 1'),
        executionDeadlineInfrastructureRow(observation: 'Registro 2'),
    ]);

    $contractNumber = new ContractNumberValueObject('08/2023');

    expect(app(ContractReadjustmentRepositoryInterface::class)->findByContractNumber($contractNumber))
        ->toHaveCount(2)
        ->and(app(ContractExecutionDeadlineRepositoryInterface::class)->findByContractNumber($contractNumber))
        ->toHaveCount(2);
});

it('returns an empty collection when a contract sheet has no data rows', function () {
    mockContractInfrastructureSheet('readjustments', [readjustmentInfrastructureHeader()]);

    expect(app(ContractReadjustmentRepositoryInterface::class)->findByContractNumber(
        new ContractNumberValueObject('08/2023'),
    ))->toBe([]);
});

it('skips one invalid readjustment row without interrupting valid records', function () {
    $invalidRow = readjustmentInfrastructureRow(apostilleNumber: '2');
    $invalidRow[7] = 'valor inválido';

    mockContractInfrastructureSheet('readjustments', [
        readjustmentInfrastructureHeader(),
        readjustmentInfrastructureRow(apostilleNumber: '1'),
        $invalidRow,
    ]);

    $records = app(ContractReadjustmentRepositoryInterface::class)->findByContractNumber(
        new ContractNumberValueObject('08/2023'),
    );

    expect($records)->toHaveCount(1)
        ->and($records[0]->apostilleNumber)->toBe('1');
});

it('translates a google sheet access failure and preserves the original cause', function () {
    $cause = new RuntimeException('Google API unavailable');

    Sheets::shouldReceive('spreadsheet')
        ->once()
        ->with(config('google_sheets.contract_spreadsheet.spreadsheet_id'))
        ->andThrow($cause);

    try {
        app(ContractSheetAdapterInterface::class)->read('contracts');
    } catch (GoogleSheetReadException $exception) {
        expect($exception->getPrevious())->toBe($cause)
            ->and($exception->spreadsheetId)->toBe(config('google_sheets.contract_spreadsheet.spreadsheet_id'))
            ->and($exception->sheet['name'])->toBe(' GERENCIADORA');

        return;
    }

    $this->fail('Expected GoogleSheetReadException was not thrown.');
});

it('translates invalid required rows into a controlled mapping exception', function () {
    expect(fn () => app(ValueAdditiveSheetMapper::class)->map([
        'N° DO CONTRATO' => '-',
        'MUNICÍPIO' => 'Salvador',
    ]))->toThrow(ContractSheetRowMappingException::class);
});

/**
 * @param  list<array<int, mixed>>  $rows
 */
function mockContractInfrastructureSheet(string $sheetKey, array $rows, int $times = 1): void
{
    $sheet = config("google_sheets.contract_spreadsheet.sheets.{$sheetKey}");
    $spreadsheetId = config('google_sheets.contract_spreadsheet.spreadsheet_id');

    Sheets::shouldReceive('spreadsheet')
        ->times($times)
        ->with($spreadsheetId)
        ->andReturnSelf();
    Sheets::shouldReceive('sheet')
        ->times($times)
        ->with("'{$sheet['name']}'")
        ->andReturnSelf();
    Sheets::shouldReceive('range')
        ->times($times)
        ->with($sheet['range'])
        ->andReturnSelf();
    Sheets::shouldReceive('get')
        ->times($times)
        ->andReturnUsing(static fn (): Collection => collect($rows));
}

/**
 * @param  list<list<array<int, mixed>>>  $rowsByRead
 */
function mockContractInfrastructureSequence(array $rowsByRead): void
{
    $spreadsheetId = config('google_sheets.contract_spreadsheet.spreadsheet_id');
    $times = count($rowsByRead);

    Sheets::shouldReceive('spreadsheet')
        ->times($times)
        ->with($spreadsheetId)
        ->andReturnSelf();
    Sheets::shouldReceive('sheet')
        ->times($times)
        ->andReturnSelf();
    Sheets::shouldReceive('range')
        ->times($times)
        ->with('A:Z')
        ->andReturnSelf();
    Sheets::shouldReceive('get')
        ->times($times)
        ->andReturnValues(array_map(static fn (array $rows): Collection => collect($rows), $rowsByRead));
}

/**
 * @return list<string>
 */
function contractInfrastructureHeader(): array
{
    return [
        'COORDENAÇÃO',
        'DATA SOLICITAÇÃO',
        'EMPRESA',
        'Nº DO CT',
        'PROCESSO SEI Nº',
        'OBJETO',
        'LOCAL',
        'STATUS',
        'SITUAÇÃO',
        'VALOR INICIAL',
        'VALOR ATUALIZADO',
        'VIGÊNCIA INÍCIO',
        'VIGÊNCIA FINAL',
        'EXECUÇÃO FINAL',
        'MUNICÍPIO',
    ];
}

/**
 * @return list<string|null>
 */
function contractInfrastructureRow(string $contractNumber = '08/2023', string $company = 'Empresa X'): array
{
    return [
        'COTEC',
        '10/01/2026',
        $company,
        $contractNumber,
        '020.1234.2026.0000001-10',
        'Construção de unidade pública',
        'SSP/GAB/DG',
        'PUBLICADO',
        'PUBLICADO',
        'R$ 100,00',
        'R$ 120,00',
        '01/02/2026',
        '31/01/2027',
        '-',
        'Feira de Santana',
    ];
}

/**
 * @return list<string>
 */
function valueAdditiveInfrastructureHeader(): array
{
    return [
        'DATA DA ENTRADA NO PROTOCOLO',
        'ETAPA',
        'N° DO CONTRATO',
        'EMPRESA',
        'MUNICÍPIO',
        'UNIDADE',
        'N° PROCESSO SEI',
        'TIPO',
        'VALOR',
        'STATUS',
        'LOCAL ATUAL',
        'TEMPO DE TRAMITAÇÃO',
        'SITUAÇÃO',
        'DATA DA PUBLICAÇÃO',
        'VALOR APÓS PUBLICAÇÃO',
        'TEMPO PUBLICAÇÃO',
        'N° DO ADITIVO',
        'OBS:',
    ];
}

/**
 * @return list<string|null>
 */
function valueAdditiveInfrastructureRow(
    string $contractNumber = '08/2023',
    string $municipality = 'FEIRA DE SANTANA',
    string $additiveNumber = '1',
    string $value = 'R$ 1.000,00',
    string $publishedValue = 'R$ 1.100,00',
    string $observation = 'Observação',
): array {
    return [
        '10/01/2026',
        'FISCALIZAÇÃO',
        $contractNumber,
        'Empresa X',
        $municipality,
        'Unidade A',
        '020.1234.2026.0000001-10',
        'ACRÉSCIMO',
        $value,
        'PUBLICADO',
        'SSP/GAB/DG',
        '-',
        'PUBLICADO',
        '10/02/2026',
        $publishedValue,
        '-',
        $additiveNumber,
        $observation,
    ];
}

/**
 * @return list<string>
 */
function readjustmentInfrastructureHeader(): array
{
    return [
        'DATA DE INGRESSO',
        'EMPRESA',
        'ENTRADA NA CEIRF',
        'ÚLTIMA MOVIMENTAÇÃO NA CEIRF',
        'N° DO CONTRATO',
        'PROCESSO SEI',
        'N° DA APOSTILA',
        'VALOR CONTEMPLADO',
        'PERÍODO DE INCIDÊNCIA CONTEMPLADO',
        'STATUS',
        'LOCAL',
        'TEMPO DE TRAMITAÇÃO',
        'DATA PUBLICAÇÃO',
        'TEMPO PUBLICAÇÃO',
        'OBS:',
        'SITUAÇÃO DO PAGAMENTO',
        'SEI DE PAGAMENTO',
    ];
}

/**
 * @return list<string|null>
 */
function readjustmentInfrastructureRow(string $apostilleNumber = '1'): array
{
    return [
        '10/01/2026',
        'Empresa X',
        '15/01/2026',
        '20/01/2026',
        '08/2023',
        '020.1234.2026.0000001-10',
        $apostilleNumber,
        'R$ 5.000,00',
        '01/01/2026 a 31/12/2026',
        'PUBLICADO',
        'SSP/GAB/DG',
        '10',
        '-',
        '-',
        'Observação',
        'PENDENTE',
        '/',
    ];
}

/**
 * @return list<string>
 */
function executionDeadlineInfrastructureHeader(): array
{
    return [
        'DATA DE ENTRADA',
        'EMPRESA',
        'CONTRATO',
        'FINAL DA VIGÊNCIA',
        'MUNICÍPIO',
        'FINAL DA EXECUÇÃO',
        'DIAS PARA VENCER EXECUÇÃO',
        'DIAS PARA VENCER VIGÊNCIA',
        'SITUAÇÃO DO CONTRATO',
        'N° DO PROCESSO SEI',
        'LOCAL',
        'STATUS ADITIVO  PRAZO',
        'TEMPO DE TRAMITAÇÃO',
        'DATA PUBLICAÇÃO',
        'TEMPO DE PUBLICAÇÃO',
        'OBS:',
    ];
}

/**
 * @return list<string|null>
 */
function executionDeadlineInfrastructureRow(string $observation = 'Observação'): array
{
    return [
        '10/01/2026',
        'Empresa X',
        '08/2023',
        '31/01/2027',
        '-',
        '31/12/2026',
        '-',
        '-',
        'EM EXECUÇÃO',
        '020.1234.2026.0000001-10',
        'SSP/GAB/DG',
        'PUBLICADO',
        '10',
        '-',
        '-',
        $observation,
    ];
}
