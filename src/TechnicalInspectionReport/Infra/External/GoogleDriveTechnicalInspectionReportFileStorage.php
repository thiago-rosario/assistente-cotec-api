<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\External;

use App\Core\Infra\External\GoogleDriveAuthenticationService;
use App\TechnicalInspectionReport\Application\DTO\StoredTechnicalInspectionReportFileDTO;
use App\TechnicalInspectionReport\Application\Interfaces\Storage\TechnicalInspectionReportFileStorageInterface;
use App\TechnicalInspectionReport\Domain\Entity\TechnicalInspectionReportEntity;
use Google\Service\Drive\DriveFile;
use InvalidArgumentException;
use RuntimeException;

class GoogleDriveTechnicalInspectionReportFileStorage implements TechnicalInspectionReportFileStorageInterface
{
    public function __construct(
        private readonly ?GoogleDriveAuthenticationService $googleAuthentication = null,
    ) {}

    public function store(
        TechnicalInspectionReportEntity $report,
        string $documentPath,
    ): StoredTechnicalInspectionReportFileDTO {
        $document = $report->document();

        if ($document === null) {
            throw new InvalidArgumentException(
                'O relatório de vistoria técnica precisa possuir um documento para ser armazenado.',
            );
        }

        if (! is_file($documentPath) || ! is_readable($documentPath)) {
            throw new InvalidArgumentException('O documento do relatório não está disponível para leitura.');
        }

        $content = file_get_contents($documentPath);

        if ($content === false) {
            throw new RuntimeException('Não foi possível ler o documento do relatório.');
        }

        $file = $this->drive()->files->create(
            new DriveFile([
                'name' => $document->originalFileName(),
                'parents' => [$this->folderId()],
                'mimeType' => $document->mimeType(),
            ]),
            [
                'data' => $content,
                'mimeType' => $document->mimeType(),
                'uploadType' => 'multipart',
                'fields' => 'id,name,mimeType,size,webViewLink',
            ],
        );

        $id = trim((string) $file->getId());

        if ($id === '') {
            throw new RuntimeException('O Google Drive não retornou o identificador do documento armazenado.');
        }

        $name = trim((string) ($file->getName() ?: $document->originalFileName()));
        $mimeType = trim((string) ($file->getMimeType() ?: $document->mimeType()));
        $sizeBytes = (int) ($file->getSize() ?: $document->sizeBytes());
        $webViewLink = trim((string) ($file->getWebViewLink() ?: $this->webViewLink($id)));

        return new StoredTechnicalInspectionReportFileDTO(
            id: $id,
            name: $name,
            mimeType: $mimeType,
            sizeBytes: $sizeBytes,
            webViewLink: $webViewLink,
        );
    }

    public function delete(StoredTechnicalInspectionReportFileDTO $storedFile): void
    {
        $id = trim($storedFile->id);

        if ($id === '') {
            throw new InvalidArgumentException('O identificador do documento do relatório é obrigatório.');
        }

        $this->drive()->files->delete($id);
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
        return ($this->googleAuthentication ?? app(GoogleDriveAuthenticationService::class))->drive();
    }

    private function webViewLink(string $id): string
    {
        return 'https://drive.google.com/file/d/'.rawurlencode($id).'/view';
    }
}
