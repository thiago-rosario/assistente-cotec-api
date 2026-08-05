<?php

use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;
use App\TechnicalInspectionReport\Domain\ValueObject\ExternalMessageIdValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\MunicipalityValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\TechnicalInspectionReportIdValueObject;
use App\TechnicalInspectionReport\Infra\Repository\DriveRepository\DeleteTechnicalInspectionReportDriveRepository;
use App\TechnicalInspectionReport\Infra\Repository\DriveRepository\ExistsTechnicalInspectionReportByExternalMessageIdDriveRepository;
use App\TechnicalInspectionReport\Infra\Repository\DriveRepository\FindAllTechnicalInspectionReportDriveRepository;
use App\TechnicalInspectionReport\Infra\Repository\DriveRepository\FindTechnicalInspectionReportByIdDriveRepository;
use App\TechnicalInspectionReport\Infra\Repository\DriveRepository\FindTechnicalInspectionReportByMunicipalityDriveRepository;
use App\TechnicalInspectionReport\Infra\Repository\DriveRepository\SaveTechnicalInspectionReportDriveRepository;
use App\TechnicalInspectionReport\Infra\Repository\DriveRepository\TechnicalInspectionReportDriveRecordRepository;
use App\TechnicalInspectionReport\Infra\Repository\Gateway\TechnicalInspectionReportGoogleDriveGatewayRepository;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\FileList;
use Revolution\Google\Client\Facades\Google;

it('persists and hydrates a technical inspection report as a Drive metadata record', function () {
    config(['technical_inspection_report.google_drive.folder_id' => 'folder-001']);

    $report = technicalInspectionReportDriveEntity();
    $files = Mockery::mock();
    $files->shouldReceive('listFiles')
        ->once()
        ->withArgs(fn (array $options): bool => str_contains($options['q'], "technical_inspection_report_id' and value='report-001"))
        ->andReturn(new FileList(['files' => []]));
    $files->shouldReceive('create')
        ->once()
        ->withArgs(function (DriveFile $metadata, array $options) use ($report): bool {
            $description = json_decode($metadata->getDescription(), true);

            return $metadata->getName() === 'technical-inspection-report-report-001.json'
                && $metadata->getParents() === ['folder-001']
                && $metadata->getMimeType() === 'application/json'
                && $metadata->getAppProperties()['technical_inspection_report_id'] === 'report-001'
                && $description['municipality'] === $report->municipality()?->value()
                && $description['status'] === 'draft'
                && $options['supportsAllDrives'] === true;
        });
    $drive = new stdClass;
    $drive->files = $files;

    Google::shouldReceive('make')->once()->with('drive')->andReturn($drive);

    (new SaveTechnicalInspectionReportDriveRepository(
        new TechnicalInspectionReportDriveRecordRepository,
    ))->save($report);

    $hydrateFiles = Mockery::mock();
    $hydrateFiles->shouldReceive('listFiles')->once()->andReturn(new FileList([
        'files' => [
            new DriveFile([
                'id' => 'record-001',
                'description' => json_encode([
                    'report_id' => 'report-001',
                    'external_message_id' => 'message-001',
                    'status' => 'draft',
                    'sei_process_decision' => 'not_provided',
                    'municipality' => 'Salvador',
                    'sei_process' => null,
                    'inspection_date' => null,
                    'responsible_person' => null,
                    'document' => null,
                    'storage_failure_reason' => null,
                ]),
            ]),
        ],
    ]));
    $hydrateDrive = new stdClass;
    $hydrateDrive->files = $hydrateFiles;

    Google::shouldReceive('make')->once()->with('drive')->andReturn($hydrateDrive);

    $hydrated = (new FindAllTechnicalInspectionReportDriveRepository(
        new TechnicalInspectionReportDriveRecordRepository,
    ))->findAll();

    expect($hydrated)->toHaveCount(1)
        ->and($hydrated[0]->id()->value())->toBe('report-001')
        ->and($hydrated[0]->municipality()?->value())->toBe('Salvador')
        ->and($hydrated[0]->hasDeclaredNoSeiProcess())->toBeTrue();
});

it('delegates all aggregate operations through the Drive gateway', function () {
    $report = technicalInspectionReportDriveEntity();
    $id = new TechnicalInspectionReportIdValueObject('report-001');
    $externalMessageId = new ExternalMessageIdValueObject('message-001');

    $save = new class extends SaveTechnicalInspectionReportDriveRepository
    {
        public ?TechnicalInspectionReportEntity $received = null;

        public function __construct() {}

        public function save(TechnicalInspectionReportEntity $report): void
        {
            $this->received = $report;
        }
    };
    $findById = new class extends FindTechnicalInspectionReportByIdDriveRepository
    {
        public ?TechnicalInspectionReportIdValueObject $received = null;

        public function __construct() {}

        public function findById(TechnicalInspectionReportIdValueObject $id): ?TechnicalInspectionReportEntity
        {
            $this->received = $id;

            return technicalInspectionReportDriveEntity();
        }
    };
    $exists = new class extends ExistsTechnicalInspectionReportByExternalMessageIdDriveRepository
    {
        public ?ExternalMessageIdValueObject $received = null;

        public function __construct() {}

        public function existsByExternalMessageId(ExternalMessageIdValueObject $externalMessageId): bool
        {
            $this->received = $externalMessageId;

            return true;
        }
    };
    $findAll = new class extends FindAllTechnicalInspectionReportDriveRepository
    {
        public bool $called = false;

        public function __construct() {}

        public function findAll(): array
        {
            $this->called = true;

            return [technicalInspectionReportDriveEntity()];
        }
    };
    $findByMunicipality = new class extends FindTechnicalInspectionReportByMunicipalityDriveRepository
    {
        public array $receivedReports = [];

        public ?string $receivedMunicipality = null;

        public function findByMunicipality(array $reports, string $municipality): array
        {
            $this->receivedReports = $reports;
            $this->receivedMunicipality = $municipality;

            return $reports;
        }
    };
    $delete = new class extends DeleteTechnicalInspectionReportDriveRepository
    {
        public ?TechnicalInspectionReportIdValueObject $received = null;

        public function __construct() {}

        public function delete(TechnicalInspectionReportIdValueObject $id): void
        {
            $this->received = $id;
        }
    };

    $gateway = new TechnicalInspectionReportGoogleDriveGatewayRepository(
        $save,
        $findById,
        $exists,
        $findAll,
        $findByMunicipality,
        $delete,
    );

    $gateway->save($report);
    $found = $gateway->findById($id);
    $isExisting = $gateway->existsByExternalMessageId($externalMessageId);
    $all = $gateway->findAll();
    $byMunicipality = $gateway->findByMunicipality('Salvador');
    $gateway->delete($id);

    expect($save->received)->toBe($report)
        ->and($findById->received)->toBe($id)
        ->and($found)->toBeInstanceOf(TechnicalInspectionReportEntity::class)
        ->and($exists->received)->toBe($externalMessageId)
        ->and($isExisting)->toBeTrue()
        ->and($findAll->called)->toBeTrue()
        ->and($all)->toHaveCount(1)
        ->and($findByMunicipality->receivedReports)->toHaveCount(1)
        ->and($findByMunicipality->receivedMunicipality)->toBe('Salvador')
        ->and($byMunicipality)->toHaveCount(1)
        ->and($delete->received)->toBe($id);
});

it('finds reports by municipality with normalized and small typo variations', function () {
    $reports = [
        technicalInspectionReportDriveEntity(municipality: 'Andaraí'),
        technicalInspectionReportDriveEntity(reportId: 'report-002', municipality: 'Catu'),
    ];

    $result = (new FindTechnicalInspectionReportByMunicipalityDriveRepository)->findByMunicipality(
        $reports,
        'ANDARAI',
    );

    expect($result)->toHaveCount(1)
        ->and($result[0]->id()->value())->toBe('report-001');
});

function technicalInspectionReportDriveEntity(
    string $reportId = 'report-001',
    string $municipality = 'Salvador',
): TechnicalInspectionReportEntity {
    return TechnicalInspectionReportEntity::start(
        id: new TechnicalInspectionReportIdValueObject($reportId),
        externalMessageId: new ExternalMessageIdValueObject('message-'.$reportId),
    )->provideMunicipality(new MunicipalityValueObject($municipality));
}
