<?php

use App\TechnicalInspectionReport\Application\DTO\SearchTechnicalInspectionReportCatalogInputDTO;
use App\TechnicalInspectionReport\Application\Usecase\FindTechnicalInspectionReportUsecase;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportGoogleSheetEntity;
use App\TechnicalInspectionReport\Domain\Repository\TechnicalInspectionReportSheetRepositoryInterface;

it('finds a report by id and normalizes the single result to a list', function () {
    $input = new SearchTechnicalInspectionReportCatalogInputDTO(
        reportId: 'report-001',
        municipality: 'Salvador',
        seiProcess: '001.7313.2026.0000001-00',
    );
    $report = technicalInspectionReportCatalogSheet();
    $repository = Mockery::mock(TechnicalInspectionReportSheetRepositoryInterface::class);
    $repository->shouldReceive('findByReportId')
        ->once()
        ->with($input)
        ->andReturn($report);

    $result = (new FindTechnicalInspectionReportUsecase($repository))($input);

    expect($result)->toBe([$report]);
});

it('delegates municipality and SEI process searches to the matching repository method', function (string $property, string $value, string $method) {
    $input = new SearchTechnicalInspectionReportCatalogInputDTO(...[$property => $value]);
    $report = technicalInspectionReportCatalogSheet();
    $repository = Mockery::mock(TechnicalInspectionReportSheetRepositoryInterface::class);
    $repository->shouldReceive($method)
        ->once()
        ->with($input)
        ->andReturn([$report]);

    $result = (new FindTechnicalInspectionReportUsecase($repository))($input);

    expect($result)->toBe([$report]);
})->with([
    'municipality' => ['municipality', 'Salvador', 'findByMunicipality'],
    'SEI process' => ['seiProcess', '001.7313.2026.0000001-00', 'findByProcessSei'],
]);

it('returns an empty list when no search filter is provided', function () {
    $repository = Mockery::mock(TechnicalInspectionReportSheetRepositoryInterface::class);

    $result = (new FindTechnicalInspectionReportUsecase($repository))(
        new SearchTechnicalInspectionReportCatalogInputDTO,
    );

    expect($result)->toBe([]);
});

function technicalInspectionReportCatalogSheet(): TechnicalInspectionReportGoogleSheetEntity
{
    return new TechnicalInspectionReportGoogleSheetEntity(
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
}
