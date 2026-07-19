<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Domain\Entity;

use App\Core\TravelReport\Domain\Trait\MethodsMagicsTrait;
use App\Core\TravelReport\Domain\Validation\DomainValidation;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * @property-read int|null $id
 * @property-read int $municipalityId
 * @property-read string $submittedByUserId
 * @property-read string $fileName
 * @property-read string $filePath
 * @property-read int|null $fileSize
 * @property-read string $seiProcess
 * @property-read string $mimeType
 * @property-read DateTimeImmutable $createdAt
 * @property-read DateTimeImmutable $updatedAt
 * @property-read DateTimeImmutable|null $deletedAt
 */
final class TravelReportEntity
{
    use MethodsMagicsTrait;

    protected readonly ?int $id;

    protected readonly int $municipalityId;

    protected readonly string $submittedByUserId;

    protected readonly string $fileName;

    protected readonly string $filePath;

    protected readonly ?int $fileSize;

    protected readonly string $mimeType;

    protected readonly string $seiProcess;

    protected readonly DateTimeImmutable $createdAt;

    protected readonly DateTimeImmutable $updatedAt;

    protected readonly ?DateTimeImmutable $deletedAt;

    public function __construct(
        ?int $id = null,
        int $municipalityId = 0,
        string $submittedByUserId = '',
        string $fileName = '',
        string $filePath = '',
        ?int $fileSize = null,
        string $mimeType = 'application/pdf',
        string $seiProcess = '',
        DateTimeInterface|string|null $createdAt = null,
        DateTimeInterface|string|null $updatedAt = null,
        DateTimeInterface|string|null $deletedAt = null,
    ) {
        $this->id = $id;
        $this->municipalityId = $municipalityId;
        $this->submittedByUserId = $submittedByUserId;
        $this->fileName = $fileName;
        $this->filePath = $filePath;
        $this->fileSize = $fileSize;
        $this->mimeType = $mimeType;
        $this->seiProcess = $seiProcess;
        $this->createdAt = self::dateTime($createdAt);
        $this->updatedAt = self::dateTime($updatedAt ?? $this->createdAt);
        $this->deletedAt = self::nullableDateTime($deletedAt);

        $this->validate();
    }

    public static function newSubmission(
        int $municipalityId,
        string $submittedByUserId,
        string $fileName,
        string $filePath,
        string $seiProcess,
        ?int $fileSize = null,
        string $mimeType = 'application/pdf',
        DateTimeInterface|string|null $submittedAt = null,
    ): self {
        $submittedAt = self::dateTime($submittedAt);

        return new self(
            municipalityId: $municipalityId,
            submittedByUserId: $submittedByUserId,
            fileName: $fileName,
            filePath: $filePath,
            fileSize: $fileSize,
            mimeType: $mimeType,
            seiProcess: $seiProcess,
            createdAt: $submittedAt,
            updatedAt: $submittedAt,
        );
    }

    public function validate(): void
    {
        DomainValidation::validateSubmittedByUserId($this->submittedByUserId);
        DomainValidation::validateFileName($this->fileName);
        DomainValidation::validateFilePath($this->filePath);
        DomainValidation::validateMunicipalityId($this->municipalityId);
        DomainValidation::validateSeiProcess($this->seiProcess);
    }

    /**
     * @return array{
     *     municipality_id: int,
     *     submitted_by_user_id: string,
     *     file_name: string,
     *     file_path: string,
     *     file_size: int|null,
     *     sei_process: string,
     *     mime_type: string,
     *     created_at: DateTimeImmutable,
     *     updated_at: DateTimeImmutable,
     *     deleted_at: DateTimeImmutable|null
     * }
     */
    public function toPersistenceArray(): array
    {
        return [
            'municipality_id' => $this->municipalityId,
            'submitted_by_user_id' => $this->submittedByUserId,
            'file_name' => $this->fileName,
            'file_path' => $this->filePath,
            'file_size' => $this->fileSize,
            'sei_process' => $this->seiProcess,
            'mime_type' => $this->mimeType,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'deleted_at' => $this->deletedAt,
        ];
    }
}
