<?php

declare(strict_types=1);

use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportGoogleSheetEntity;
use App\TechnicalInspectionReport\Domain\Repository\TechnicalInspectionReportSheetRepositoryInterface;

it('represents the columns used by the technical inspection spreadsheet', function () {
    $sheet = new TechnicalInspectionReportGoogleSheetEntity(
        reportId: 'report-001',
        documentName: 'relatorio-vistoria.pdf',
        municipality: 'Salvador',
        seiProcess: null,
        hasSeiProcess: false,
        inspectionDate: '22/07/2026',
        responsiblePerson: 'João Silva',
        documentLink: 'https://drive.google.com/file/d/drive-file-001/view',
    );

    expect($sheet->toSheetRow())->toBe([
        TechnicalInspectionReportGoogleSheetEntity::ReportIdColumn => 'report-001',
        TechnicalInspectionReportGoogleSheetEntity::ReportNameColumn => 'relatorio-vistoria.pdf',
        TechnicalInspectionReportGoogleSheetEntity::MunicipalityColumn => 'Salvador',
        TechnicalInspectionReportGoogleSheetEntity::SeiProcessColumn => '',
        TechnicalInspectionReportGoogleSheetEntity::HasSeiProcessColumn => 'Não',
        TechnicalInspectionReportGoogleSheetEntity::InspectionDateColumn => '22/07/2026',
        TechnicalInspectionReportGoogleSheetEntity::ResponsiblePersonColumn => 'João Silva',
        TechnicalInspectionReportGoogleSheetEntity::DocumentLinkColumn => 'https://drive.google.com/file/d/drive-file-001/view',
    ])->and($sheet->toOrderedSheetRow())->toHaveCount(8)
        ->and($sheet->rowNumber())->toBeNull();
});

it('keeps the spreadsheet row number for future updates', function () {
    $sheet = new TechnicalInspectionReportGoogleSheetEntity(
        reportId: 'report-001',
        documentName: 'relatorio-vistoria.pdf',
        municipality: 'Salvador',
        seiProcess: '012.3456.2026.0001234-00',
        hasSeiProcess: true,
        inspectionDate: '22/07/2026',
        responsiblePerson: 'João Silva',
        documentLink: 'https://drive.google.com/file/d/drive-file-001/view',
    );

    $updated = $sheet->withRowNumber(2);

    expect($updated->rowNumber())->toBe(2)
        ->and($updated->reportId)->toBe($sheet->reportId)
        ->and($updated->hasSeiProcess)->toBeTrue()
        ->and($updated->seiProcess)->toBe($sheet->seiProcess);
});

it('reconstructs a sheet entity from the actual spreadsheet row', function () {
    $sheet = TechnicalInspectionReportGoogleSheetEntity::fromSheetRow([
        TechnicalInspectionReportGoogleSheetEntity::ReportIdColumn => 'report-001',
        TechnicalInspectionReportGoogleSheetEntity::ReportNameColumn => 'relatorio-vistoria.pdf',
        TechnicalInspectionReportGoogleSheetEntity::MunicipalityColumn => 'Salvador',
        TechnicalInspectionReportGoogleSheetEntity::SeiProcessColumn => '',
        TechnicalInspectionReportGoogleSheetEntity::HasSeiProcessColumn => 'Não',
        TechnicalInspectionReportGoogleSheetEntity::InspectionDateColumn => '22/07/2026',
        TechnicalInspectionReportGoogleSheetEntity::ResponsiblePersonColumn => 'João Silva',
        TechnicalInspectionReportGoogleSheetEntity::DocumentLinkColumn => 'https://drive.google.com/file/d/drive-file-001/view',
    ], rowNumber: 2);

    expect($sheet->reportId)->toBe('report-001')
        ->and($sheet->documentName)->toBe('relatorio-vistoria.pdf')
        ->and($sheet->hasSeiProcess)->toBeFalse()
        ->and($sheet->rowNumber())->toBe(2)
        ->and($sheet->seiProcess)->toBeNull();
});

it('infers an existing SEI process when the spreadsheet flag is empty', function () {
    $sheet = TechnicalInspectionReportGoogleSheetEntity::fromSheetRow([
        TechnicalInspectionReportGoogleSheetEntity::ReportIdColumn => 'report-001',
        TechnicalInspectionReportGoogleSheetEntity::ReportNameColumn => 'relatorio-vistoria.pdf',
        TechnicalInspectionReportGoogleSheetEntity::MunicipalityColumn => 'Salvador',
        TechnicalInspectionReportGoogleSheetEntity::SeiProcessColumn => '012.3456.2026.0001234-00',
        TechnicalInspectionReportGoogleSheetEntity::HasSeiProcessColumn => '',
        TechnicalInspectionReportGoogleSheetEntity::InspectionDateColumn => '22/07/2026',
        TechnicalInspectionReportGoogleSheetEntity::ResponsiblePersonColumn => 'João Silva',
        TechnicalInspectionReportGoogleSheetEntity::DocumentLinkColumn => 'https://drive.google.com/file/d/drive-file-001/view',
    ], rowNumber: 2);

    expect($sheet->hasSeiProcess)->toBeTrue();
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
        documentName: 'relatorio-vistoria.pdf',
        municipality: 'Salvador',
        seiProcess: null,
        hasSeiProcess: false,
        inspectionDate: '22/07/2026',
        responsiblePerson: 'João Silva',
        documentLink: 'https://drive.google.com/file/d/drive-file-001/view',
    );
})->throws(InvalidArgumentException::class);
