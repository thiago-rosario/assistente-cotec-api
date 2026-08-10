<?php

use App\TechnicalInspectionReport\Application\DTO\RegisterTechnicalInspectionReportCatalogInputDTO;
use App\TechnicalInspectionReport\Application\DTO\StoredTechnicalInspectionReportFileDTO;
use App\TechnicalInspectionReport\Application\DTO\StoreTechnicalInspectionReportInputDTO;
use App\TechnicalInspectionReport\Application\Interfaces\Factory\TechnicalInspectionReportGoogleSheetFactoryInterface;
use App\TechnicalInspectionReport\Application\Interfaces\Storage\TechnicalInspectionReportFileStorageInterface;
use App\TechnicalInspectionReport\Application\Usecase\StoreTechnicalInspectionReportUsecase;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportGoogleSheetEntity;
use App\TechnicalInspectionReport\Domain\Repository\TechnicalInspectionReportSheetRepositoryInterface;
use App\TechnicalInspectionReport\Domain\ValueObject\ExternalMessageIdValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\InspectionDateValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\MunicipalityValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\ResponsiblePersonValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\TechnicalInspectionReportFileValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\TechnicalInspectionReportIdValueObject;

it('stores the PDF, creates the sheet entry and registers it', function () {
    $report = applicationTechnicalInspectionReportForStorage();
    $storedFile = storedTechnicalInspectionReportFile();
    $catalogEntry = new TechnicalInspectionReportGoogleSheetEntity(
        reportId: 'report-001',
        documentName: 'relatorio-vistoria.pdf',
        municipality: 'Salvador',
        seiProcess: null,
        hasSeiProcess: false,
        inspectionDate: '22/07/2026',
        responsiblePerson: 'João Silva',
        documentLink: 'https://drive.google.com/file/d/drive-file-001/view',
    );

    $fileStorage = Mockery::mock(TechnicalInspectionReportFileStorageInterface::class);
    $fileStorage->shouldReceive('store')
        ->once()
        ->with($report, 'storage/reports/report-001.pdf')
        ->andReturn($storedFile);

    $factory = Mockery::mock(TechnicalInspectionReportGoogleSheetFactoryInterface::class);
    $factory->shouldReceive('create')
        ->once()
        ->with($report, $storedFile)
        ->andReturn($catalogEntry);

    $sheetRepository = Mockery::mock(TechnicalInspectionReportSheetRepositoryInterface::class);
    $sheetRepository->shouldReceive('register')
        ->once()
        ->with(Mockery::on(function (RegisterTechnicalInspectionReportCatalogInputDTO $input) use ($catalogEntry): bool {
            return $input->sheet === $catalogEntry;
        }));

    $output = (new StoreTechnicalInspectionReportUsecase(
        fileStorage: $fileStorage,
        factory: $factory,
        sheetRepository: $sheetRepository,
    ))(
        new StoreTechnicalInspectionReportInputDTO(
            report: $report,
            documentPath: 'storage/reports/report-001.pdf',
        ),
    );

    expect($output->report)->toBe($report)
        ->and($output->storedFile)->toBe($storedFile)
        ->and($output->catalogEntry)->toBe($catalogEntry);
});

it('does not call Sheets when Drive storage fails', function () {
    $report = applicationTechnicalInspectionReportForStorage();
    $storageException = new RuntimeException('Google Drive indisponível.');

    $fileStorage = Mockery::mock(TechnicalInspectionReportFileStorageInterface::class);
    $fileStorage->shouldReceive('store')
        ->once()
        ->andThrow($storageException);

    $factory = Mockery::mock(TechnicalInspectionReportGoogleSheetFactoryInterface::class);
    $factory->shouldReceive('create')->never();

    $sheetRepository = Mockery::mock(TechnicalInspectionReportSheetRepositoryInterface::class);
    $sheetRepository->shouldReceive('register')->never();

    expect(fn () => (new StoreTechnicalInspectionReportUsecase(
        fileStorage: $fileStorage,
        factory: $factory,
        sheetRepository: $sheetRepository,
    ))(
        new StoreTechnicalInspectionReportInputDTO(
            report: $report,
            documentPath: 'storage/reports/report-001.pdf',
        ),
    ))->toThrow($storageException);
});

it('propagates a Sheets registration failure after the file is stored', function () {
    $report = applicationTechnicalInspectionReportForStorage();
    $storedFile = storedTechnicalInspectionReportFile();
    $catalogEntry = new TechnicalInspectionReportGoogleSheetEntity(
        reportId: 'report-001',
        documentName: 'relatorio-vistoria.pdf',
        municipality: 'Salvador',
        seiProcess: null,
        hasSeiProcess: false,
        inspectionDate: '22/07/2026',
        responsiblePerson: 'João Silva',
        documentLink: 'https://drive.google.com/file/d/drive-file-001/view',
    );
    $sheetException = new RuntimeException('Google Sheets indisponível.');

    $fileStorage = Mockery::mock(TechnicalInspectionReportFileStorageInterface::class);
    $fileStorage->shouldReceive('store')->once()->andReturn($storedFile);
    $fileStorage->shouldReceive('delete')->once()->with($storedFile);

    $factory = Mockery::mock(TechnicalInspectionReportGoogleSheetFactoryInterface::class);
    $factory->shouldReceive('create')->once()->andReturn($catalogEntry);

    $sheetRepository = Mockery::mock(TechnicalInspectionReportSheetRepositoryInterface::class);
    $sheetRepository->shouldReceive('register')->once()->andThrow($sheetException);

    expect(fn () => (new StoreTechnicalInspectionReportUsecase(
        fileStorage: $fileStorage,
        factory: $factory,
        sheetRepository: $sheetRepository,
    ))(
        new StoreTechnicalInspectionReportInputDTO(
            report: $report,
            documentPath: 'storage/reports/report-001.pdf',
        ),
    ))->toThrow($sheetException);
});

function applicationTechnicalInspectionReportForStorage(): TechnicalInspectionReportEntity
{
    return TechnicalInspectionReportEntity::start(
        id: new TechnicalInspectionReportIdValueObject('report-001'),
        externalMessageId: new ExternalMessageIdValueObject('message-001'),
    )
        ->provideMunicipality(new MunicipalityValueObject('Salvador'))
        ->declareNoSeiProcess()
        ->provideInspectionDate(InspectionDateValueObject::fromBrazilianFormat('22/07/2026'))
        ->provideResponsiblePerson(new ResponsiblePersonValueObject('João Silva'))
        ->attachDocument(new TechnicalInspectionReportFileValueObject(
            originalFileName: 'relatorio-vistoria.pdf',
            mimeType: TechnicalInspectionReportFileValueObject::PdfMimeType,
            sizeBytes: 2048,
        ));
}

function storedTechnicalInspectionReportFile(): StoredTechnicalInspectionReportFileDTO
{
    return new StoredTechnicalInspectionReportFileDTO(
        id: 'drive-file-001',
        name: 'relatorio-vistoria.pdf',
        mimeType: TechnicalInspectionReportFileValueObject::PdfMimeType,
        sizeBytes: 2048,
        webViewLink: 'https://drive.google.com/file/d/drive-file-001/view',
    );
}
