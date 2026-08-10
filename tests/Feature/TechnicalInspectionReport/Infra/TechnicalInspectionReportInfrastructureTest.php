<?php

use App\TechnicalInspectionReport\Application\DTO\RegisterTechnicalInspectionReportCatalogInputDTO;
use App\TechnicalInspectionReport\Application\DTO\StoredTechnicalInspectionReportFileDTO;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportGoogleSheetEntity;
use App\TechnicalInspectionReport\Domain\ValueObject\ExternalMessageIdValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\InspectionDateValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\MunicipalityValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\ResponsiblePersonValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\SeiProcessValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\TechnicalInspectionReportFileValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\TechnicalInspectionReportIdValueObject;
use App\TechnicalInspectionReport\Infra\External\GoogleDriveTechnicalInspectionReportFileStorage;
use App\TechnicalInspectionReport\Infra\Repository\SheetRepository\FindAllTechnicalInspectionReportGoogleSheetRepository;
use App\TechnicalInspectionReport\Infra\Repository\SheetRepository\FindTechnicalInspectionReportByMunicipalityGoogleSheetRepository;
use App\TechnicalInspectionReport\Infra\Repository\SheetRepository\FindTechnicalInspectionReportByProcessSeiGoogleSheetRepository;
use App\TechnicalInspectionReport\Infra\Repository\SheetRepository\FindTechnicalInspectionReportByReportIdGoogleSheetRepository;
use App\TechnicalInspectionReport\Infra\Repository\SheetRepository\RegisterTechnicalInspectionReportGoogleSheetRepository;
use App\TechnicalInspectionReport\Infra\Repository\SheetRepository\UpdateTechnicalInspectionReportGoogleSheetRepository;
use Google\Service\Drive\DriveFile;
use Revolution\Google\Client\Facades\Google;
use Revolution\Google\Sheets\Facades\Sheets;

it('reads technical inspection report rows from the configured sheet', function () {
    config([
        'technical_inspection_report.google_sheet.spreadsheet_id' => 'spreadsheet-001',
        'technical_inspection_report.google_sheet.sheet_name' => 'RELATORIOS',
    ]);

    Sheets::shouldReceive('spreadsheet')->once()->with('spreadsheet-001')->andReturnSelf();
    Sheets::shouldReceive('sheet')->once()->with('RELATORIOS')->andReturnSelf();
    Sheets::shouldReceive('range')->once()->with('A:ZZ')->andReturnSelf();
    Sheets::shouldReceive('get')->once()->andReturn(collect([
        ['Título da planilha'],
        [
            'ID DO RELATÓRIO',
            'Nome do relatório',
            'Município',
            TechnicalInspectionReportGoogleSheetEntity::SeiProcessColumn,
            'Possui processo SEI',
            'Data da viagem',
            TechnicalInspectionReportGoogleSheetEntity::ResponsiblePersonColumn,
            'Link do relatório',
        ],
        ['report-001', 'report.pdf', 'Andaraí', '020.4487.2021.0009714-69', 'Sim', '22/07/2026', 'João Silva', 'https://drive.google.com/file/d/drive-001/view'],
        ['', '', '', '', '', '', '', ''],
    ]));

    $reports = (new FindAllTechnicalInspectionReportGoogleSheetRepository)->findAllSheet();

    expect($reports)->toHaveCount(1)
        ->and($reports[0]->reportId)->toBe('report-001')
        ->and($reports[0]->municipality)->toBe('Andaraí')
        ->and($reports[0]->rowNumber())->toBe(3);
});

it('filters technical inspection reports with the same municipality and process conventions as the build panel', function () {
    $reports = [
        technicalInspectionReportSheet(municipality: 'Andaraí', seiProcess: '020.4487.2021.0009714-69'),
        technicalInspectionReportSheet(reportId: 'report-002', municipality: 'Catu', seiProcess: null),
    ];

    $municipalityReports = (new FindTechnicalInspectionReportByMunicipalityGoogleSheetRepository)
        ->findByMunicipality($reports, 'ANDARAI');
    $processReports = (new FindTechnicalInspectionReportByProcessSeiGoogleSheetRepository)
        ->findByProcessSei($reports, '020.4487.2021.0009714-69');
    $report = (new FindTechnicalInspectionReportByReportIdGoogleSheetRepository)
        ->findByReportId($reports, 'report-002');

    expect($municipalityReports)->toHaveCount(1)
        ->and($processReports)->toHaveCount(1)
        ->and($processReports[0]->reportId)->toBe('report-001')
        ->and($report?->municipality)->toBe('Catu');
});

it('registers a technical inspection report using the configured sheet columns', function () {
    config([
        'technical_inspection_report.google_sheet.spreadsheet_id' => 'spreadsheet-001',
        'technical_inspection_report.google_sheet.sheet_name' => 'RELATORIOS',
    ]);

    $sheet = technicalInspectionReportSheet();

    Sheets::shouldReceive('spreadsheet')->once()->with('spreadsheet-001')->andReturnSelf();
    Sheets::shouldReceive('sheet')->once()->with('RELATORIOS')->andReturnSelf();
    Sheets::shouldReceive('append')->once()->with([$sheet->toOrderedSheetRow()]);

    (new RegisterTechnicalInspectionReportGoogleSheetRepository)->register(
        new RegisterTechnicalInspectionReportCatalogInputDTO(sheet: $sheet),
    );
});

it('updates the row number attached to a technical inspection report', function () {
    config([
        'technical_inspection_report.google_sheet.spreadsheet_id' => 'spreadsheet-001',
        'technical_inspection_report.google_sheet.sheet_name' => 'RELATORIOS',
    ]);

    $sheet = technicalInspectionReportSheet()->withRowNumber(7);

    Sheets::shouldReceive('spreadsheet')->once()->with('spreadsheet-001')->andReturnSelf();
    Sheets::shouldReceive('sheet')->once()->with('RELATORIOS')->andReturnSelf();
    Sheets::shouldReceive('range')->once()->with('A7:H7')->andReturnSelf();
    Sheets::shouldReceive('update')->once()->with([$sheet->toOrderedSheetRow()]);

    (new UpdateTechnicalInspectionReportGoogleSheetRepository(
        new FindAllTechnicalInspectionReportGoogleSheetRepository,
    ))->update(
        new RegisterTechnicalInspectionReportCatalogInputDTO(sheet: $sheet),
    );
});

it('stores a PDF in the configured Google Drive folder', function () {
    config(['technical_inspection_report.google_drive.folder_id' => 'folder-001']);

    $files = Mockery::mock();
    $files->shouldReceive('create')
        ->once()
        ->withArgs(function (DriveFile $metadata, array $options): bool {
            return $metadata->getName() === 'report.pdf'
                && $metadata->getParents() === ['folder-001']
                && $metadata->getMimeType() === TechnicalInspectionReportFileValueObject::PdfMimeType
                && $options['mimeType'] === TechnicalInspectionReportFileValueObject::PdfMimeType
                && $options['uploadType'] === 'multipart'
                && $options['fields'] === 'id,name,mimeType,size,webViewLink'
                && $options['data'] === file_get_contents(__FILE__);
        })
        ->andReturn(new DriveFile([
            'id' => 'drive-001',
            'name' => 'report.pdf',
            'mimeType' => TechnicalInspectionReportFileValueObject::PdfMimeType,
            'size' => '123',
            'webViewLink' => 'https://drive.google.com/file/d/drive-001/view',
        ]));

    $drive = new stdClass;
    $drive->files = $files;

    Google::shouldReceive('make')->once()->with('drive')->andReturn($drive);

    $storedFile = (new GoogleDriveTechnicalInspectionReportFileStorage)->store(
        technicalInspectionReportWithDocument(),
        __FILE__,
    );

    expect($storedFile->id)->toBe('drive-001')
        ->and($storedFile->sizeBytes)->toBe(123)
        ->and($storedFile->webViewLink)->toBe('https://drive.google.com/file/d/drive-001/view');
});

it('deletes a stored technical inspection report document from Google Drive', function () {
    $files = Mockery::mock();
    $files->shouldReceive('delete')->once()->with('drive-001');

    $drive = new stdClass;
    $drive->files = $files;

    Google::shouldReceive('make')->once()->with('drive')->andReturn($drive);

    (new GoogleDriveTechnicalInspectionReportFileStorage)->delete(
        new StoredTechnicalInspectionReportFileDTO(
            id: 'drive-001',
            name: 'report.pdf',
            mimeType: TechnicalInspectionReportFileValueObject::PdfMimeType,
            sizeBytes: 123,
            webViewLink: 'https://drive.google.com/file/d/drive-001/view',
        ),
    );

    expect(true)->toBeTrue();
});

function technicalInspectionReportSheet(
    string $reportId = 'report-001',
    string $municipality = 'Salvador',
    ?string $seiProcess = '020.4487.2021.0009714-69',
): TechnicalInspectionReportGoogleSheetEntity {
    return new TechnicalInspectionReportGoogleSheetEntity(
        reportId: $reportId,
        documentName: 'report.pdf',
        municipality: $municipality,
        seiProcess: $seiProcess,
        hasSeiProcess: $seiProcess !== null,
        inspectionDate: '22/07/2026',
        responsiblePerson: 'João Silva',
        documentLink: 'https://drive.google.com/file/d/drive-'.$reportId.'/view',
    );
}

function technicalInspectionReportWithDocument(): TechnicalInspectionReportEntity
{
    return TechnicalInspectionReportEntity::start(
        id: new TechnicalInspectionReportIdValueObject('report-001'),
        externalMessageId: new ExternalMessageIdValueObject('message-001'),
    )
        ->provideMunicipality(new MunicipalityValueObject('Salvador'))
        ->provideSeiProcess(new SeiProcessValueObject('020.4487.2021.0009714-69'))
        ->provideInspectionDate(InspectionDateValueObject::fromBrazilianFormat('22/07/2026'))
        ->provideResponsiblePerson(new ResponsiblePersonValueObject('João Silva'))
        ->attachDocument(new TechnicalInspectionReportFileValueObject('report.pdf', 'application/pdf', 123));
}
