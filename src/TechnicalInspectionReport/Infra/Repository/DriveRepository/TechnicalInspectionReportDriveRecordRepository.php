<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Repository\DriveRepository;

use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;
use App\TechnicalInspectionReport\Domain\ValueObject\ExternalMessageIdValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\InspectionDateValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\MunicipalityValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\ResponsiblePersonValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\SeiProcessValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\TechnicalInspectionReportFileValueObject;
use App\TechnicalInspectionReport\Domain\ValueObject\TechnicalInspectionReportIdValueObject;
use App\TechnicalInspectionReport\Enum\SeiProcessDecision;
use App\TechnicalInspectionReport\Enum\TechnicalInspectionReportStatus;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JsonException;
use Revolution\Google\Client\Facades\Google;
use RuntimeException;

final class TechnicalInspectionReportDriveRecordRepository
{
    private const string RecordTypeKey = 'cotec_record_type';

    private const string RecordType = 'technical_inspection_report';

    private const string ReportIdKey = 'technical_inspection_report_id';

    private const string ExternalMessageIdKey = 'technical_inspection_report_external_message_id';

    private const string MetadataMimeType = 'application/json';

    private ?object $drive = null;

    public function save(TechnicalInspectionReportEntity $report): void
    {
        $existingFile = $this->findFileByReportId($report->id()->value());
        $metadata = $this->metadata($report, $existingFile === null);

        if ($existingFile === null) {
            $this->drive()->files->create($metadata, [
                'fields' => 'id',
                'supportsAllDrives' => true,
            ]);

            return;
        }

        $fileId = trim((string) $existingFile->getId());

        if ($fileId === '') {
            throw new RuntimeException('O registro do relatório no Google Drive não possui identificador.');
        }

        $this->drive()->files->update($fileId, $metadata, [
            'fields' => 'id',
            'supportsAllDrives' => true,
        ]);
    }

    public function findFileByReportId(string $reportId): ?DriveFile
    {
        return $this->queryFiles($this->propertyQuery(self::ReportIdKey, $reportId))[0] ?? null;
    }

    public function findFileByExternalMessageId(string $externalMessageId): ?DriveFile
    {
        return $this->queryFiles($this->propertyQuery(self::ExternalMessageIdKey, $externalMessageId))[0] ?? null;
    }

    /**
     * @return list<DriveFile>
     */
    public function findFiles(): array
    {
        return $this->queryFiles();
    }

    /**
     * @return list<TechnicalInspectionReportEntity>
     */
    public function findReports(): array
    {
        return array_map(
            fn (DriveFile $file): TechnicalInspectionReportEntity => $this->toEntity($file),
            $this->findFiles(),
        );
    }

    public function findReportById(string $reportId): ?TechnicalInspectionReportEntity
    {
        $file = $this->findFileByReportId($reportId);

        return $file === null ? null : $this->toEntity($file);
    }

    public function existsByExternalMessageId(string $externalMessageId): bool
    {
        return $this->findFileByExternalMessageId($externalMessageId) !== null;
    }

    public function deleteByReportId(string $reportId): void
    {
        $file = $this->findFileByReportId($reportId);

        if ($file === null) {
            return;
        }

        $fileId = trim((string) $file->getId());

        if ($fileId === '') {
            throw new RuntimeException('O registro do relatório no Google Drive não possui identificador.');
        }

        $this->drive()->files->delete($fileId, ['supportsAllDrives' => true]);
    }

    private function metadata(TechnicalInspectionReportEntity $report, bool $includeParents): DriveFile
    {
        $metadata = [
            'name' => 'technical-inspection-report-'.$report->id()->value().'.json',
            'mimeType' => self::MetadataMimeType,
            'description' => $this->encode($this->toArray($report)),
            'appProperties' => [
                self::RecordTypeKey => self::RecordType,
                self::ReportIdKey => $report->id()->value(),
                self::ExternalMessageIdKey => $report->externalMessageId()->value(),
            ],
        ];

        if ($includeParents) {
            $metadata['parents'] = [$this->folderId()];
        }

        return new DriveFile($metadata);
    }

    /**
     * @return list<DriveFile>
     */
    private function queryFiles(string $extraQuery = ''): array
    {
        $query = $this->baseQuery();

        if ($extraQuery !== '') {
            $query .= ' and '.$extraQuery;
        }

        $files = [];
        $pageToken = null;

        do {
            $options = [
                'q' => $query,
                'spaces' => 'drive',
                'pageSize' => 100,
                'fields' => 'nextPageToken,files(id,name,description,appProperties,mimeType)',
                'supportsAllDrives' => true,
                'includeItemsFromAllDrives' => true,
            ];

            if ($pageToken !== null) {
                $options['pageToken'] = $pageToken;
            }

            $response = $this->drive()->files->listFiles($options);
            $files = [...$files, ...($response->getFiles() ?: [])];
            $pageToken = $response->getNextPageToken();
        } while (filled($pageToken));

        return $files;
    }

    private function baseQuery(): string
    {
        return sprintf(
            "'%s' in parents and appProperties has { key='%s' and value='%s' } and trashed = false",
            $this->escapeQueryValue($this->folderId()),
            self::RecordTypeKey,
            self::RecordType,
        );
    }

    private function propertyQuery(string $key, string $value): string
    {
        return sprintf(
            "appProperties has { key='%s' and value='%s' }",
            $key,
            $this->escapeQueryValue($value),
        );
    }

    private function folderId(): string
    {
        $folderId = trim((string) config('technical_inspection_report.google_drive.folder_id'));

        if ($folderId === '') {
            throw new InvalidArgumentException(
                'O identificador da pasta do Google Drive não está configurado.',
            );
        }

        return $folderId;
    }

    private function drive(): object
    {
        return $this->drive ??= $this->createDrive();
    }

    private function createDrive(): object
    {
        $drive = Google::make('drive');

        if (! is_object($drive)) {
            throw new RuntimeException('Não foi possível criar o serviço do Google Drive.');
        }

        return $drive;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function encode(array $data): string
    {
        try {
            return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $exception) {
            throw new RuntimeException('Não foi possível serializar o relatório para o Google Drive.', 0, $exception);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(TechnicalInspectionReportEntity $report): array
    {
        $document = $report->document();

        return [
            'report_id' => $report->id()->value(),
            'external_message_id' => $report->externalMessageId()->value(),
            'status' => $report->status()->value,
            'sei_process_decision' => $report->hasSeiProcessDecision()
                ? ($report->hasSeiProcess() ? SeiProcessDecision::Provided->value : SeiProcessDecision::NotProvided->value)
                : SeiProcessDecision::Pending->value,
            'municipality' => $report->municipality()?->value(),
            'sei_process' => $report->seiProcess()?->value(),
            'inspection_date' => $report->inspectionDate()?->formatted(),
            'responsible_person' => $report->responsiblePerson()?->value(),
            'document' => $document === null ? null : [
                'original_file_name' => $document->originalFileName(),
                'mime_type' => $document->mimeType(),
                'size_bytes' => $document->sizeBytes(),
            ],
            'storage_failure_reason' => $report->storageFailureReason(),
        ];
    }

    private function toEntity(DriveFile $file): TechnicalInspectionReportEntity
    {
        $description = trim((string) $file->getDescription());

        if ($description === '') {
            throw new RuntimeException('O registro do relatório no Google Drive não possui conteúdo.');
        }

        try {
            $data = json_decode($description, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('O registro do relatório no Google Drive possui conteúdo inválido.', 0, $exception);
        }

        if (! is_array($data)) {
            throw new RuntimeException('O registro do relatório no Google Drive possui formato inválido.');
        }

        $report = TechnicalInspectionReportEntity::start(
            id: new TechnicalInspectionReportIdValueObject($this->required($data, 'report_id')),
            externalMessageId: new ExternalMessageIdValueObject($this->required($data, 'external_message_id')),
        );

        if (filled($data['municipality'] ?? null)) {
            $report->provideMunicipality(new MunicipalityValueObject((string) $data['municipality']));
        }

        $decision = SeiProcessDecision::tryFrom((string) ($data['sei_process_decision'] ?? SeiProcessDecision::Pending->value));

        if ($decision === null) {
            throw new RuntimeException('O registro do relatório possui decisão de processo SEI inválida.');
        }

        if ($decision === SeiProcessDecision::Provided && filled($data['sei_process'] ?? null)) {
            $report->provideSeiProcess(new SeiProcessValueObject((string) $data['sei_process']));
        } elseif ($decision === SeiProcessDecision::NotProvided) {
            $report->declareNoSeiProcess();
        }

        if (filled($data['inspection_date'] ?? null)) {
            $report->provideInspectionDate(InspectionDateValueObject::fromBrazilianFormat((string) $data['inspection_date']));
        }

        if (filled($data['responsible_person'] ?? null)) {
            $report->provideResponsiblePerson(new ResponsiblePersonValueObject((string) $data['responsible_person']));
        }

        if (is_array($data['document'] ?? null)) {
            $document = $data['document'];
            $report->attachDocument(new TechnicalInspectionReportFileValueObject(
                originalFileName: $this->required($document, 'original_file_name'),
                mimeType: $this->required($document, 'mime_type'),
                sizeBytes: (int) ($document['size_bytes'] ?? 0),
            ));
        }

        $this->restoreStatus($report, (string) ($data['status'] ?? TechnicalInspectionReportStatus::Draft->value), $data['storage_failure_reason'] ?? null);

        return $report;
    }

    private function restoreStatus(TechnicalInspectionReportEntity $report, string $status, mixed $failureReason): void
    {
        $status = TechnicalInspectionReportStatus::tryFrom($status);

        if ($status === null) {
            throw new RuntimeException('O registro do relatório possui status inválido.');
        }

        if ($status === TechnicalInspectionReportStatus::Draft) {
            return;
        }

        $report->markReadyForStorage();

        if ($status === TechnicalInspectionReportStatus::ReadyForStorage) {
            return;
        }

        $report->beginStorage();

        if ($status === TechnicalInspectionReportStatus::StoragePending) {
            return;
        }

        if ($status === TechnicalInspectionReportStatus::Stored) {
            $report->confirmStorage();

            return;
        }

        $report->registerStorageFailure(is_scalar($failureReason) ? (string) $failureReason : null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function required(array $data, string $key): string
    {
        $value = trim((string) ($data[$key] ?? ''));

        if ($value === '') {
            throw new RuntimeException(sprintf('O campo %s do registro do relatório é obrigatório.', $key));
        }

        return $value;
    }

    private function escapeQueryValue(string $value): string
    {
        return Str::of($value)
            ->replace('\\', '\\\\')
            ->replace("'", "\\'")
            ->toString();
    }
}
