<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Domain\ValueObject;

use App\TechnicalInspectionReport\Exception\InvalidTechnicalInspectionReportFileException;

class TechnicalInspectionReportFileValueObject
{
    public const string PdfMimeType = 'application/pdf';

    private string $originalFileName;

    private string $mimeType;

    private int $sizeBytes;

    public function __construct(string $originalFileName, string $mimeType, int $sizeBytes)
    {
        $originalFileName = trim($originalFileName);
        $mimeType = mb_strtolower(trim($mimeType));

        if ($originalFileName === '') {
            throw new InvalidTechnicalInspectionReportFileException(
                'O nome original do documento do relatório é obrigatório.',
            );
        }

        if ($mimeType !== self::PdfMimeType) {
            throw new InvalidTechnicalInspectionReportFileException(
                'O documento do relatório deve possuir o tipo MIME application/pdf.',
            );
        }

        if ($sizeBytes <= 0) {
            throw new InvalidTechnicalInspectionReportFileException(
                'O tamanho do documento do relatório deve ser maior que zero.',
            );
        }

        $this->originalFileName = $originalFileName;
        $this->mimeType = $mimeType;
        $this->sizeBytes = $sizeBytes;
    }

    public function originalFileName(): string
    {
        return $this->originalFileName;
    }

    public function mimeType(): string
    {
        return $this->mimeType;
    }

    public function sizeBytes(): int
    {
        return $this->sizeBytes;
    }

    public function isPdf(): bool
    {
        return $this->mimeType === self::PdfMimeType;
    }

    public function equals(self $file): bool
    {
        return $this->originalFileName === $file->originalFileName
            && $this->mimeType === $file->mimeType
            && $this->sizeBytes === $file->sizeBytes;
    }
}
