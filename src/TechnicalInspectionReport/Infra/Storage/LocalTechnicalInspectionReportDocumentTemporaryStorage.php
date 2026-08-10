<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Storage;

use App\Core\Domain\Entity\MessageDocumentEntity;
use App\TechnicalInspectionReport\Application\DTO\TechnicalInspectionReportTemporaryFileDTO;
use App\TechnicalInspectionReport\Application\Interfaces\Storage\TechnicalInspectionReportDocumentTemporaryStorageInterface;
use App\TechnicalInspectionReport\Exception\InvalidTechnicalInspectionReportFileException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use RuntimeException;

final class LocalTechnicalInspectionReportDocumentTemporaryStorage implements TechnicalInspectionReportDocumentTemporaryStorageInterface
{
    public function __construct(
        private readonly FilesystemManager $filesystems,
    ) {}

    public function store(
        MessageDocumentEntity $document,
        string $reportId,
    ): TechnicalInspectionReportTemporaryFileDTO {
        $mimeType = mb_strtolower(trim($document->mimeType()));

        if ($mimeType !== 'application/pdf') {
            throw new InvalidTechnicalInspectionReportFileException(
                'Envie o relatório como um documento PDF.',
            );
        }

        $content = $this->content($document);
        $sizeBytes = strlen($content);
        $maxSizeBytes = (int) config('technical_inspection_report.max_document_size_bytes', 10 * 1024 * 1024);

        if ($sizeBytes === 0 || ($maxSizeBytes > 0 && $sizeBytes > $maxSizeBytes)) {
            throw new InvalidTechnicalInspectionReportFileException(
                'O PDF do relatório está vazio ou excede o tamanho máximo permitido.',
            );
        }

        if (! str_starts_with($content, '%PDF-')) {
            throw new InvalidTechnicalInspectionReportFileException(
                'O arquivo enviado não possui um conteúdo PDF válido.',
            );
        }

        $path = 'technical-inspection-reports/'.$reportId.'.pdf';

        if (! $this->disk()->put($path, $content)) {
            throw new RuntimeException('Não foi possível guardar temporariamente o PDF do relatório.');
        }

        return new TechnicalInspectionReportTemporaryFileDTO($path, $sizeBytes);
    }

    public function absolutePath(string $path): string
    {
        return $this->disk()->path($path);
    }

    public function delete(string $path): void
    {
        if (trim($path) !== '') {
            $this->disk()->delete($path);
        }
    }

    private function content(MessageDocumentEntity $document): string
    {
        if (filled($document->contentBase64())) {
            $encoded = preg_replace(
                '/^data:application\/pdf;base64,/i',
                '',
                trim((string) $document->contentBase64()),
            ) ?? trim((string) $document->contentBase64());
            $encoded = preg_replace('/\s+/', '', $encoded) ?? $encoded;
            $decoded = base64_decode($encoded, true);

            if ($decoded === false) {
                throw new InvalidTechnicalInspectionReportFileException(
                    'O conteúdo Base64 do PDF é inválido.',
                );
            }

            return $decoded;
        }

        $path = $document->temporaryPath();

        if ($path === null || ! is_file($path) || ! is_readable($path)) {
            throw new InvalidTechnicalInspectionReportFileException(
                'O PDF do relatório não está disponível para leitura.',
            );
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new InvalidTechnicalInspectionReportFileException(
                'Não foi possível ler o PDF do relatório.',
            );
        }

        return $content;
    }

    private function disk(): Filesystem
    {
        return $this->filesystems->disk('local');
    }
}
