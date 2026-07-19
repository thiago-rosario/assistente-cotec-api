<?php

use App\Core\TravelReport\Domain\Entity\TravelReportEntity;
use App\Core\TravelReport\Domain\Repository\TravelReportRepositoryInterface;
use App\Core\TravelReport\Infra\Repository\EloquentRepository\DeleteTravelReportEloquentRepository;
use App\Core\TravelReport\Infra\Repository\EloquentRepository\FindAllTravelReportsEloquentRepository;
use App\Core\TravelReport\Infra\Repository\EloquentRepository\FindTravelReportByIdEloquentRepository;
use App\Core\TravelReport\Infra\Repository\EloquentRepository\FindTravelReportByMunicipalityIdEloquentRepository;
use App\Core\TravelReport\Infra\Repository\EloquentRepository\FindTravelReportBySeiProcessEloquentRepository;
use App\Core\TravelReport\Infra\Repository\EloquentRepository\FindTravelReportBySubmittedByUserIdEloquentRepository;
use App\Core\TravelReport\Infra\Repository\EloquentRepository\TravelReportInsertEloquentRepository;
use App\Core\TravelReport\Infra\Repository\Gateway\TravelReportGatewayRepository;
use App\Models\TravelReport;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    Schema::dropIfExists('travel_report_documents');
    Schema::dropIfExists('municipalities');

    $municipalitiesMigration = require database_path('migrations/TravelReport/2026_07_16_152329_create_municipalities_table.php');
    $travelReportDocumentsMigration = require database_path('migrations/TravelReport/2026_07_16_152330_create_travel_report_documents_table.php');

    $municipalitiesMigration->up();
    $travelReportDocumentsMigration->up();
});

it('inserts a travel report through the eloquent repository', function (): void {
    $submittedAt = new DateTimeImmutable('2026-07-20 08:30:00');

    travelReportRepositoryTestInsertMunicipality(1, 'Acajutiba', $submittedAt);

    $travelReport = TravelReportEntity::newSubmission(
        municipalityId: 1,
        submittedByUserId: 'user-1',
        fileName: 'relatorio.pdf',
        filePath: 'travel-reports/relatorio.pdf',
        seiProcess: 'SEI-12345',
        fileSize: 2048,
        mimeType: 'application/pdf',
        submittedAt: $submittedAt,
    );

    $createdTravelReport = (new TravelReportInsertEloquentRepository)->insert($travelReport);

    $model = TravelReport::query()->first();

    expect($createdTravelReport)->toBeInstanceOf(TravelReportEntity::class)
        ->and($createdTravelReport->id)->toBe(1)
        ->and($createdTravelReport->municipalityId)->toBe(1)
        ->and($createdTravelReport->submittedByUserId)->toBe('user-1')
        ->and($createdTravelReport->fileName)->toBe('relatorio.pdf')
        ->and($createdTravelReport->filePath)->toBe('travel-reports/relatorio.pdf')
        ->and($createdTravelReport->fileSize)->toBe(2048)
        ->and($createdTravelReport->mimeType)->toBe('application/pdf')
        ->and($createdTravelReport->seiProcess)->toBe('SEI-12345')
        ->and($createdTravelReport->createdAt->format('Y-m-d H:i:s'))->toBe('2026-07-20 08:30:00')
        ->and($createdTravelReport->updatedAt->format('Y-m-d H:i:s'))->toBe('2026-07-20 08:30:00')
        ->and($createdTravelReport->deletedAt)->toBeNull()
        ->and($model)->toBeInstanceOf(TravelReport::class)
        ->and($model->getTable())->toBe('travel_report_documents')
        ->and($model->file_name)->toBe('relatorio.pdf');
});

it('delegates all travel report repository methods through the gateway', function (): void {
    $submittedAt = new DateTimeImmutable('2026-07-20 08:30:00');
    $gateway = travelReportRepositoryTestGateway();

    travelReportRepositoryTestInsertMunicipality(1, 'Acajutiba', $submittedAt);
    travelReportRepositoryTestInsertMunicipality(2, 'Alagoinhas', $submittedAt);

    $firstReport = $gateway->insert(travelReportRepositoryTestSubmission(
        municipalityId: 1,
        submittedByUserId: 'user-1',
        fileName: 'relatorio-1.pdf',
        filePath: 'travel-reports/relatorio-1.pdf',
        seiProcess: 'SEI-001',
        submittedAt: $submittedAt,
    ));

    $secondReport = $gateway->insert(travelReportRepositoryTestSubmission(
        municipalityId: 1,
        submittedByUserId: 'user-2',
        fileName: 'relatorio-2.pdf',
        filePath: 'travel-reports/relatorio-2.pdf',
        seiProcess: 'SEI-002',
        submittedAt: $submittedAt,
    ));

    $thirdReport = $gateway->insert(travelReportRepositoryTestSubmission(
        municipalityId: 2,
        submittedByUserId: 'user-1',
        fileName: 'relatorio-3.pdf',
        filePath: 'travel-reports/relatorio-3.pdf',
        seiProcess: 'SEI-003',
        submittedAt: $submittedAt,
    ));

    expect($gateway)->toBeInstanceOf(TravelReportRepositoryInterface::class)
        ->and($gateway->all())->toHaveCount(3)
        ->and($gateway->findById($firstReport->id))->toBeInstanceOf(TravelReportEntity::class)
        ->and($gateway->findById($firstReport->id)->seiProcess)->toBe('SEI-001')
        ->and($gateway->findBySeiProcess('SEI-002'))->toBeInstanceOf(TravelReportEntity::class)
        ->and($gateway->findBySeiProcess('SEI-002')->id)->toBe($secondReport->id)
        ->and($gateway->findBySubmittedByUserId('user-1'))->toHaveCount(2)
        ->and($gateway->findBySubmittedByUserId('user-1')[1]->id)->toBe($thirdReport->id)
        ->and($gateway->findByMunicipalityId(1))->toHaveCount(2)
        ->and($gateway->findByMunicipalityId(1)[0]->id)->toBe($firstReport->id);

    expect($gateway->delete($secondReport->id))->toBeTrue()
        ->and($gateway->findById($secondReport->id))->toBeNull()
        ->and($gateway->delete(999))->toBeFalse()
        ->and(TravelReport::withTrashed()->find($secondReport->id)->trashed())->toBeTrue();
});

function travelReportRepositoryTestGateway(): TravelReportGatewayRepository
{
    return new TravelReportGatewayRepository(
        travelReportInsertRepository: new TravelReportInsertEloquentRepository,
        findTravelReportByIdRepository: new FindTravelReportByIdEloquentRepository,
        findAllTravelReportsRepository: new FindAllTravelReportsEloquentRepository,
        findTravelReportBySeiProcessRepository: new FindTravelReportBySeiProcessEloquentRepository,
        findTravelReportBySubmittedByUserIdRepository: new FindTravelReportBySubmittedByUserIdEloquentRepository,
        findTravelReportByMunicipalityIdRepository: new FindTravelReportByMunicipalityIdEloquentRepository,
        deleteTravelReportRepository: new DeleteTravelReportEloquentRepository,
    );
}

function travelReportRepositoryTestInsertMunicipality(
    int $id,
    string $name,
    DateTimeImmutable $submittedAt,
): void {
    DB::table('municipalities')->insert([
        'id' => $id,
        'name' => $name,
        'created_at' => $submittedAt,
        'updated_at' => $submittedAt,
    ]);
}

function travelReportRepositoryTestSubmission(
    int $municipalityId,
    string $submittedByUserId,
    string $fileName,
    string $filePath,
    string $seiProcess,
    DateTimeImmutable $submittedAt,
): TravelReportEntity {
    return TravelReportEntity::newSubmission(
        municipalityId: $municipalityId,
        submittedByUserId: $submittedByUserId,
        fileName: $fileName,
        filePath: $filePath,
        seiProcess: $seiProcess,
        fileSize: 2048,
        mimeType: 'application/pdf',
        submittedAt: $submittedAt,
    );
}
