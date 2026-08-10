<?php

use App\TechnicalInspectionReport\Application\DTO\StoredTechnicalInspectionReportFileDTO;
use App\TechnicalInspectionReport\Application\Factory\TechnicalInspectionReportGoogleSheetFactory;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;
use App\TechnicalInspectionReport\Domain\ValueObject\ExternalMessageIdValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\InspectionDateValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\MunicipalityValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\ResponsiblePersonValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\SeiProcessValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\TechnicalInspectionReportFileValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\TechnicalInspectionReportIdValueObject;

it('extracts the report data and the Drive link into a sheet entity', function () {
    $report = TechnicalInspectionReportEntity::start(
        id: new TechnicalInspectionReportIdValueObject('report-001'),
        externalMessageId: new ExternalMessageIdValueObject('message-001'),
    )
        ->provideMunicipality(new MunicipalityValueObject('Salvador'))
        ->provideSeiProcess(new SeiProcessValueObject('012.3456.2026.0001234-00'))
        ->provideInspectionDate(InspectionDateValueObject::fromBrazilianFormat('22/07/2026'))
        ->provideResponsiblePerson(new ResponsiblePersonValueObject('João Silva'))
        ->attachDocument(new TechnicalInspectionReportFileValueObject(
            originalFileName: 'relatorio-original.pdf',
            mimeType: TechnicalInspectionReportFileValueObject::PdfMimeType,
            sizeBytes: 2048,
        ));
    $storedFile = new StoredTechnicalInspectionReportFileDTO(
        id: 'drive-file-001',
        name: 'relatorio-vistoria.pdf',
        mimeType: TechnicalInspectionReportFileValueObject::PdfMimeType,
        sizeBytes: 2048,
        webViewLink: 'https://drive.google.com/file/d/drive-file-001/view',
    );

    $sheet = (new TechnicalInspectionReportGoogleSheetFactory)->create($report, $storedFile);

    expect($sheet->toSheetRow())->toBe([
        'ID DO RELATÓRIO' => 'report-001',
        'NOME DO RELATÓRIO' => 'relatorio-original.pdf',
        'MUNICÍPIO' => 'Salvador',
        'PROCESSO SEI' => '012.3456.2026.0001234-00',
        'POSSUI PROCESSO SEI' => 'Sim',
        'DATA DA VIAGEM' => '22/07/2026',
        'RESPONSÁVEL' => 'João Silva',
        'LINK DO RELATÓRIO' => 'https://drive.google.com/file/d/drive-file-001/view',
    ]);
});

it('leaves the SEI cell empty when the report declares there is no process', function () {
    $report = TechnicalInspectionReportEntity::start(
        id: new TechnicalInspectionReportIdValueObject('report-001'),
        externalMessageId: new ExternalMessageIdValueObject('message-001'),
    )
        ->provideMunicipality(new MunicipalityValueObject('Salvador'))
        ->declareNoSeiProcess()
        ->provideInspectionDate(InspectionDateValueObject::fromBrazilianFormat('22/07/2026'))
        ->provideResponsiblePerson(new ResponsiblePersonValueObject('João Silva'))
        ->attachDocument(new TechnicalInspectionReportFileValueObject(
            originalFileName: 'relatorio-original.pdf',
            mimeType: TechnicalInspectionReportFileValueObject::PdfMimeType,
            sizeBytes: 2048,
        ));

    $sheet = (new TechnicalInspectionReportGoogleSheetFactory)->create(
        $report,
        new StoredTechnicalInspectionReportFileDTO(
            id: 'drive-file-001',
            name: 'relatorio-vistoria.pdf',
            mimeType: TechnicalInspectionReportFileValueObject::PdfMimeType,
            sizeBytes: 2048,
            webViewLink: 'https://drive.google.com/file/d/drive-file-001/view',
        ),
    );

    expect($sheet->seiProcess)->toBeNull()
        ->and($sheet->hasSeiProcess)->toBeFalse()
        ->and($sheet->toSheetRow()['PROCESSO SEI'])->toBe('')
        ->and($sheet->toSheetRow()['POSSUI PROCESSO SEI'])->toBe('Não');
});
