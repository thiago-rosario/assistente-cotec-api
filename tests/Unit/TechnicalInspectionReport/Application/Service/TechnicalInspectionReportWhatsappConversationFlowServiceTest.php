<?php

use App\Core\Domain\Entity\MessageDocumentEntity;
use App\Core\Domain\Entity\MessageEntity;
use App\Core\Domain\Repository\WhatsappConversationStateRepositoryInterface;
use App\Core\Enum\WhatsappConversationState;
use App\Core\Infra\Message\WhatsappMainMenuMessageBuilder;
use App\TechnicalInspectionReport\Application\DTO\StoredTechnicalInspectionReportFileDTO;
use App\TechnicalInspectionReport\Application\DTO\StoreTechnicalInspectionReportOutputDTO;
use App\TechnicalInspectionReport\Application\Factory\TechnicalInspectionReportDraftFactory;
use App\TechnicalInspectionReport\Application\Interfaces\Builder\TechnicalInspectionReportDraftBuilderInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Usecase\FindTechnicalInspectionReportUsecaseInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Usecase\StoreTechnicalInspectionReportUsecaseInterface;
use App\TechnicalInspectionReport\Application\Service\TechnicalInspectionReportWhatsappConversationFlowService;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportGoogleSheetEntity;
use App\TechnicalInspectionReport\Domain\Repository\TechnicalInspectionReportDraftRepositoryInterface;
use App\TechnicalInspectionReport\Infra\Message\TechnicalInspectionReportWhatsappMessageBuilder;
use App\TechnicalInspectionReport\Infra\Storage\LocalTechnicalInspectionReportDocumentTemporaryStorage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config(['cache.default' => 'array']);
    Cache::flush();
    Storage::fake('local');
    $this->flowState = app(WhatsappConversationStateRepositoryInterface::class);
});

it('collects, confirms and stores a technical inspection report without SEI process', function () {
    $storedFile = storedTechnicalInspectionReportFileForWhatsapp();
    $storeReport = Mockery::mock(StoreTechnicalInspectionReportUsecaseInterface::class);
    $storeReport->shouldReceive('__invoke')
        ->once()
        ->with(Mockery::on(function ($input): bool {
            return $input->report->isComplete()
                && is_file($input->documentPath)
                && $input->report->municipality()?->value() === 'Salvador'
                && $input->report->hasDeclaredNoSeiProcess();
        }))
        ->andReturnUsing(function ($input) use ($storedFile): StoreTechnicalInspectionReportOutputDTO {
            return new StoreTechnicalInspectionReportOutputDTO(
                report: $input->report,
                storedFile: $storedFile,
                catalogEntry: technicalInspectionReportSheetEntryForWhatsapp(),
            );
        });
    $flow = technicalInspectionReportWhatsappFlow($storeReport);
    $phone = '5511999999999';
    $start = new MessageEntity('2', $phone, externalId: 'message-start');

    expect($flow->start($start)['intent'])->toBe('technical_inspection_report_menu');
    expect($flow->respondTo(new MessageEntity('1', $phone))['intent'])
        ->toBe('technical_inspection_report_started');
    expect($flow->respondTo(new MessageEntity('Salvador', $phone))['intent'])
        ->toBe('technical_inspection_report_awaiting_sei_decision');
    expect($flow->respondTo(new MessageEntity('1', $phone))['intent'])
        ->toBe('technical_inspection_report_awaiting_inspection_date');
    expect($flow->respondTo(new MessageEntity('22/07/2026', $phone))['intent'])
        ->toBe('technical_inspection_report_awaiting_responsible');
    expect($flow->respondTo(new MessageEntity('João Silva', $phone))['intent'])
        ->toBe('technical_inspection_report_awaiting_document');

    $document = new MessageDocumentEntity(
        originalFileName: 'relatorio-vistoria.pdf',
        mimeType: 'application/pdf',
        sizeBytes: 0,
        contentBase64: base64_encode("%PDF-1.4\nconteudo"),
    );

    expect($flow->respondTo(new MessageEntity(null, $phone, $document))['intent'])
        ->toBe('technical_inspection_report_awaiting_confirmation');
    $storedResponse = $flow->respondTo(new MessageEntity('1', $phone));

    expect($storedResponse['intent'])->toBe('technical_inspection_report_stored')
        ->and($storedResponse['reply'])->toContain('✅ *Relatório armazenado com sucesso!*')
        ->and($storedResponse['reply'])->toContain('📍 Município: Salvador')
        ->and($storedResponse['reply'])->toContain('📄 Processo SEI: Não possui processo SEI vinculado')
        ->and($storedResponse['reply'])->toContain('📅 Data da vistoria: 22/07/2026')
        ->and($storedResponse['reply'])->toContain('👤 Responsável: João Silva')
        ->and($storedResponse['reply'])->toContain('🔗 Acessar relatório:');
    expect($this->flowState->get(new MessageEntity(null, $phone)))
        ->toBe(WhatsappConversationState::TechnicalInspectionReportMenu);
    expect(app(TechnicalInspectionReportDraftRepositoryInterface::class)
        ->get(new MessageEntity(null, $phone)))->toBeNull();
});

it('fully resets a report registration when the user sends 0', function () {
    $flow = technicalInspectionReportWhatsappFlow(
        Mockery::mock(StoreTechnicalInspectionReportUsecaseInterface::class),
    );
    $phone = '5511999999999';

    $flow->start(new MessageEntity('2', $phone));
    $flow->respondTo(new MessageEntity('1', $phone));

    $response = $flow->respondTo(new MessageEntity('0', $phone));

    expect($response['intent'])->toBe('conversation_ended')
        ->and($this->flowState->get(new MessageEntity(null, $phone)))->toBeNull()
        ->and(app(TechnicalInspectionReportDraftRepositoryInterface::class)
            ->get(new MessageEntity(null, $phone)))->toBeNull();
});

it('rejects a non-PDF and keeps the conversation waiting for the document', function () {
    $storeReport = Mockery::mock(StoreTechnicalInspectionReportUsecaseInterface::class);
    $storeReport->shouldReceive('__invoke')->never();
    $flow = technicalInspectionReportWhatsappFlow($storeReport);
    $phone = '5511888888888';
    $message = new MessageEntity('2', $phone);

    $flow->start($message);
    $flow->respondTo(new MessageEntity('1', $phone));
    $flow->respondTo(new MessageEntity('Salvador', $phone));
    $flow->respondTo(new MessageEntity('não', $phone));
    $flow->respondTo(new MessageEntity('22/07/2026', $phone));
    $flow->respondTo(new MessageEntity('João Silva', $phone));

    $response = $flow->respondTo(new MessageEntity(null, $phone, new MessageDocumentEntity(
        originalFileName: 'relatorio.txt',
        mimeType: 'text/plain',
        sizeBytes: 10,
        contentBase64: base64_encode('texto'),
    )));

    expect($response['intent'])->toBe('technical_inspection_report_invalid_document')
        ->and($this->flowState->get(new MessageEntity(null, $phone)))
        ->toBe(WhatsappConversationState::TechnicalInspectionReportAwaitingDocument);
});

it('collects and stores the SEI process when the user confirms it exists', function () {
    $storedFile = storedTechnicalInspectionReportFileForWhatsapp();
    $storeReport = Mockery::mock(StoreTechnicalInspectionReportUsecaseInterface::class);
    $storeReport->shouldReceive('__invoke')
        ->once()
        ->with(Mockery::on(function ($input): bool {
            return $input->report->hasSeiProcess()
                && $input->report->seiProcess()?->value() === '020.4487.2021.0009714-69';
        }))
        ->andReturnUsing(function ($input) use ($storedFile): StoreTechnicalInspectionReportOutputDTO {
            return new StoreTechnicalInspectionReportOutputDTO(
                report: $input->report,
                storedFile: $storedFile,
                catalogEntry: technicalInspectionReportSheetEntryForWhatsapp(),
            );
        });
    $flow = technicalInspectionReportWhatsappFlow($storeReport);
    $phone = '5511666666666';

    $flow->start(new MessageEntity('2', $phone));
    $flow->respondTo(new MessageEntity('1', $phone));
    $flow->respondTo(new MessageEntity('Salvador', $phone));
    expect($flow->respondTo(new MessageEntity('sim', $phone))['intent'])
        ->toBe('technical_inspection_report_awaiting_sei_process');
    expect($flow->respondTo(new MessageEntity('processo inválido', $phone))['intent'])
        ->toBe('technical_inspection_report_invalid_sei_process');
    $flow->respondTo(new MessageEntity('020.4487.2021.0009714-69', $phone));
    $flow->respondTo(new MessageEntity('22/07/2026', $phone));
    $flow->respondTo(new MessageEntity('João Silva', $phone));
    $flow->respondTo(new MessageEntity(null, $phone, new MessageDocumentEntity(
        originalFileName: 'relatorio-vistoria.pdf',
        mimeType: 'application/pdf',
        sizeBytes: 0,
        contentBase64: base64_encode("%PDF-1.4\nconteudo"),
    )));

    expect($flow->respondTo(new MessageEntity('1', $phone))['intent'])
        ->toBe('technical_inspection_report_stored');
});

it('keeps the draft for a recoverable storage failure and retries it', function () {
    $storedFile = storedTechnicalInspectionReportFileForWhatsapp();
    $storeReport = Mockery::mock(StoreTechnicalInspectionReportUsecaseInterface::class);
    $attempt = 0;
    $storeReport->shouldReceive('__invoke')
        ->twice()
        ->andReturnUsing(function ($input) use (&$attempt, $storedFile): StoreTechnicalInspectionReportOutputDTO {
            $attempt++;

            if ($attempt === 1) {
                throw new RuntimeException('Sheets indisponível.');
            }

            return new StoreTechnicalInspectionReportOutputDTO(
                report: $input->report,
                storedFile: $storedFile,
                catalogEntry: technicalInspectionReportSheetEntryForWhatsapp(),
            );
        });
    $flow = technicalInspectionReportWhatsappFlow($storeReport);
    $phone = '5511777777777';
    $flow->start(new MessageEntity('2', $phone));
    $flow->respondTo(new MessageEntity('1', $phone));
    $flow->respondTo(new MessageEntity('Salvador', $phone));
    $flow->respondTo(new MessageEntity('não', $phone));
    $flow->respondTo(new MessageEntity('22/07/2026', $phone));
    $flow->respondTo(new MessageEntity('João Silva', $phone));
    $flow->respondTo(new MessageEntity(null, $phone, new MessageDocumentEntity(
        originalFileName: 'relatorio-vistoria.pdf',
        mimeType: 'application/pdf',
        sizeBytes: 0,
        contentBase64: base64_encode("%PDF-1.4\nconteudo"),
    )));

    expect($flow->respondTo(new MessageEntity('1', $phone))['intent'])
        ->toBe('technical_inspection_report_recoverable_failure');
    expect($this->flowState->get(new MessageEntity(null, $phone)))
        ->toBe(WhatsappConversationState::TechnicalInspectionReportRecoverableFailure);
    expect($flow->respondTo(new MessageEntity('1', $phone))['intent'])
        ->toBe('technical_inspection_report_stored');
});

it('consults technical inspection reports by municipality and formats every result', function () {
    $findReports = Mockery::mock(FindTechnicalInspectionReportUsecaseInterface::class);
    $findReports->shouldReceive('__invoke')
        ->once()
        ->with(Mockery::on(fn ($input): bool => $input->municipality === 'Salvador'
            && $input->seiProcess === null
            && $input->reportId === null))
        ->andReturn([
            technicalInspectionReportSheetEntryForWhatsapp(),
            new TechnicalInspectionReportGoogleSheetEntity(
                reportId: 'report-whatsapp-2',
                documentName: 'relatorio-vistoria-2.pdf',
                municipality: 'Salvador',
                seiProcess: null,
                hasSeiProcess: false,
                inspectionDate: '23/07/2026',
                responsiblePerson: 'Maria Souza',
                documentLink: 'https://drive.google.com/file/d/drive-file-whatsapp-2/view',
            ),
        ]);
    $flow = technicalInspectionReportWhatsappFlow(
        Mockery::mock(StoreTechnicalInspectionReportUsecaseInterface::class),
        $findReports,
    );
    $phone = '5511999999999';

    expect($flow->start(new MessageEntity('2', $phone))['intent'])
        ->toBe('technical_inspection_report_menu');
    expect($flow->respondTo(new MessageEntity('2', $phone))['intent'])
        ->toBe('technical_inspection_report_awaiting_search_municipality');

    $response = $flow->respondTo(new MessageEntity('Salvador', $phone));

    expect($response['intent'])->toBe('technical_inspection_report_results')
        ->and($response['total'])->toBe(2)
        ->and($response['filters'])->toBe(['municipality' => 'Salvador'])
        ->and($response['reply'])->toContain('✅ 2 relatórios encontrados:')
        ->and($response['reply'])->toContain('📄 *Relatório 1*')
        ->and($response['reply'])->toContain('📄 *Relatório 2*')
        ->and($response['reply'])->toContain('🔗 Acessar relatório:')
        ->and($response['reply'])->toContain('Não possui processo SEI vinculado')
        ->and($this->flowState->get(new MessageEntity(null, $phone)))
        ->toBe(WhatsappConversationState::TechnicalInspectionReportMenu);
});

it('explains when no technical inspection report exists for the municipality', function () {
    $findReports = Mockery::mock(FindTechnicalInspectionReportUsecaseInterface::class);
    $findReports->shouldReceive('__invoke')->once()->andReturn([]);
    $flow = technicalInspectionReportWhatsappFlow(
        Mockery::mock(StoreTechnicalInspectionReportUsecaseInterface::class),
        $findReports,
    );
    $phone = '5511888888888';

    $flow->start(new MessageEntity('2', $phone));
    $flow->respondTo(new MessageEntity('2', $phone));
    $response = $flow->respondTo(new MessageEntity('Catu', $phone));

    expect($response['intent'])->toBe('technical_inspection_report_no_results')
        ->and($response['total'])->toBe(0)
        ->and($response['reply'])->toContain('❌ Não encontrei relatórios')
        ->and($response['reply'])->toContain('📍 Município: Catu');
});

function technicalInspectionReportWhatsappFlow(
    StoreTechnicalInspectionReportUsecaseInterface $storeReport,
    ?FindTechnicalInspectionReportUsecaseInterface $findReports = null,
): TechnicalInspectionReportWhatsappConversationFlowService {
    $findReports ??= Mockery::mock(FindTechnicalInspectionReportUsecaseInterface::class);

    return new TechnicalInspectionReportWhatsappConversationFlowService(
        conversationStates: app(WhatsappConversationStateRepositoryInterface::class),
        drafts: app(TechnicalInspectionReportDraftRepositoryInterface::class),
        temporaryStorage: new LocalTechnicalInspectionReportDocumentTemporaryStorage(app('filesystem')),
        findReports: $findReports,
        storeReport: $storeReport,
        draftFactory: new TechnicalInspectionReportDraftFactory,
        draftBuilder: app(TechnicalInspectionReportDraftBuilderInterface::class),
        messages: new TechnicalInspectionReportWhatsappMessageBuilder,
        mainMenu: new WhatsappMainMenuMessageBuilder,
    );
}

function storedTechnicalInspectionReportFileForWhatsapp(): StoredTechnicalInspectionReportFileDTO
{
    return new StoredTechnicalInspectionReportFileDTO(
        id: 'drive-file-whatsapp',
        name: 'relatorio-vistoria.pdf',
        mimeType: 'application/pdf',
        sizeBytes: 16,
        webViewLink: 'https://drive.google.com/file/d/drive-file-whatsapp/view',
    );
}

function technicalInspectionReportSheetEntryForWhatsapp(): TechnicalInspectionReportGoogleSheetEntity
{
    return new TechnicalInspectionReportGoogleSheetEntity(
        reportId: 'report-whatsapp',
        documentName: 'relatorio-vistoria.pdf',
        municipality: 'Salvador',
        seiProcess: null,
        hasSeiProcess: false,
        inspectionDate: '22/07/2026',
        responsiblePerson: 'João Silva',
        documentLink: 'https://drive.google.com/file/d/drive-file-whatsapp/view',
    );
}
