<?php

declare(strict_types=1);

use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportGoogleSheetEntity;
use App\TechnicalInspectionReport\Domain\Repository\TechnicalInspectionReportSheetRepositoryInterface;

it('represents all report data required by the technical inspection sheet', function () {
    $sheet = new TechnicalInspectionReportGoogleSheetEntity(
        reportId: 'report-001',
        externalMessageId: 'message-001',
        municipality: 'Salvador',
        seiProcess: null,
        inspectionDate: '22/07/2026',
        responsiblePerson: 'João Silva',
        documentName: 'relatorio-vistoria.pdf',
        documentId: 'drive-file-001',
        documentLink: 'https://drive.google.com/file/d/drive-file-001/view',
    );

    expect($sheet->toSheetRow())->toBe([
        TechnicalInspectionReportGoogleSheetEntity::ReportIdColumn => 'report-001',
        TechnicalInspectionReportGoogleSheetEntity::ExternalMessageIdColumn => 'message-001',
        TechnicalInspectionReportGoogleSheetEntity::MunicipalityColumn => 'Salvador',
        TechnicalInspectionReportGoogleSheetEntity::SeiProcessColumn => '',
        TechnicalInspectionReportGoogleSheetEntity::InspectionDateColumn => '22/07/2026',
        TechnicalInspectionReportGoogleSheetEntity::ResponsiblePersonColumn => 'João Silva',
        TechnicalInspectionReportGoogleSheetEntity::DocumentNameColumn => 'relatorio-vistoria.pdf',
        TechnicalInspectionReportGoogleSheetEntity::DocumentIdColumn => 'drive-file-001',
        TechnicalInspectionReportGoogleSheetEntity::DocumentLinkColumn => 'https://drive.google.com/file/d/drive-file-001/view',
    ])->and($sheet->toOrderedSheetRow())->toHaveCount(9)
        ->and($sheet->rowNumber())->toBeNull();
});

it('keeps the spreadsheet row number for future updates', function () {
    $sheet = new TechnicalInspectionReportGoogleSheetEntity(
        reportId: 'report-001',
        externalMessageId: 'message-001',
        municipality: 'Salvador',
        seiProcess: '012.3456.2026.0001234-00',
        inspectionDate: '22/07/2026',
        responsiblePerson: 'João Silva',
        documentName: 'relatorio-vistoria.pdf',
        documentId: 'drive-file-001',
        documentLink: 'https://drive.google.com/file/d/drive-file-001/view',
    );

    $updated = $sheet->withRowNumber(2);

    expect($updated->rowNumber())->toBe(2)
        ->and($updated->reportId)->toBe($sheet->reportId)
        ->and($updated->seiProcess)->toBe($sheet->seiProcess);
});

it('reconstructs a sheet entity from a header-mapped row', function () {
    $sheet = TechnicalInspectionReportGoogleSheetEntity::fromSheetRow([
        TechnicalInspectionReportGoogleSheetEntity::ReportIdColumn => 'report-001',
        TechnicalInspectionReportGoogleSheetEntity::ExternalMessageIdColumn => 'message-001',
        TechnicalInspectionReportGoogleSheetEntity::MunicipalityColumn => 'Salvador',
        TechnicalInspectionReportGoogleSheetEntity::SeiProcessColumn => '',
        TechnicalInspectionReportGoogleSheetEntity::InspectionDateColumn => '22/07/2026',
        TechnicalInspectionReportGoogleSheetEntity::ResponsiblePersonColumn => 'João Silva',
        TechnicalInspectionReportGoogleSheetEntity::DocumentNameColumn => 'relatorio-vistoria.pdf',
        TechnicalInspectionReportGoogleSheetEntity::DocumentIdColumn => 'drive-file-001',
        TechnicalInspectionReportGoogleSheetEntity::DocumentLinkColumn => 'https://drive.google.com/file/d/drive-file-001/view',
    ], rowNumber: 2);

    expect($sheet->reportId)->toBe('report-001')
        ->and($sheet->rowNumber())->toBe(2)
        ->and($sheet->seiProcess)->toBeNull();
});

it('exposes registration, lookup and update operations through the sheet contract', function () {
    expect(get_class_methods(TechnicalInspectionReportSheetRepositoryInterface::class))
        ->toContain('register')
        ->toContain('findByReportId')
        ->toContain('findByMunicipality')
        ->toContain('findByProcessSei')
        ->toContain('update')
        ->not->toContain('findByMunicipaluity');
});

it('rejects incomplete spreadsheet rows', function () {
    new TechnicalInspectionReportGoogleSheetEntity(
        reportId: '',
        externalMessageId: 'message-001',
        municipality: 'Salvador',
        seiProcess: null,
        inspectionDate: '22/07/2026',
        responsiblePerson: 'João Silva',
        documentName: 'relatorio-vistoria.pdf',
        documentId: 'drive-file-001',
        documentLink: 'https://drive.google.com/file/d/drive-file-001/view',
    );
})->throws(InvalidArgumentException::class);
