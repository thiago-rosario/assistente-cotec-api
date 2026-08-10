<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\DTO;

use InvalidArgumentException;

readonly class TechnicalInspectionReportDraftDTO
{
    public function __construct(
        public string $reportId,
        public string $externalMessageId,
        public ?string $municipality = null,
        public ?bool $hasSeiProcess = null,
        public ?string $seiProcess = null,
        public ?string $inspectionDate = null,
        public ?string $responsiblePerson = null,
        public ?string $documentPath = null,
        public ?string $documentName = null,
        public ?string $documentMimeType = null,
        public ?int $documentSizeBytes = null,
    ) {}

    public function withMunicipality(string $municipality): self
    {
        return new self(...array_merge($this->toArray(), ['municipality' => $municipality]));
    }

    public function withSeiProcess(string $seiProcess): self
    {
        return new self(...array_merge($this->toArray(), [
            'hasSeiProcess' => true,
            'seiProcess' => $seiProcess,
        ]));
    }

    public function awaitingSeiProcess(): self
    {
        return new self(...array_merge($this->toArray(), [
            'hasSeiProcess' => true,
            'seiProcess' => null,
        ]));
    }

    public function withoutSeiProcess(): self
    {
        return new self(...array_merge($this->toArray(), [
            'hasSeiProcess' => false,
            'seiProcess' => null,
        ]));
    }

    public function withInspectionDate(string $inspectionDate): self
    {
        return new self(...array_merge($this->toArray(), ['inspectionDate' => $inspectionDate]));
    }

    public function withResponsiblePerson(string $responsiblePerson): self
    {
        return new self(...array_merge($this->toArray(), ['responsiblePerson' => $responsiblePerson]));
    }

    public function withDocument(
        string $documentPath,
        string $documentName,
        string $documentMimeType,
        int $documentSizeBytes,
    ): self {
        return new self(...array_merge($this->toArray(), compact(
            'documentPath',
            'documentName',
            'documentMimeType',
            'documentSizeBytes',
        )));
    }

    public function hasDocument(): bool
    {
        return filled($this->documentPath)
            && filled($this->documentName)
            && filled($this->documentMimeType)
            && ($this->documentSizeBytes ?? 0) > 0;
    }

    public function isComplete(): bool
    {
        return filled($this->municipality)
            && $this->hasSeiProcess !== null
            && ($this->hasSeiProcess === false || filled($this->seiProcess))
            && filled($this->inspectionDate)
            && filled($this->responsiblePerson)
            && $this->hasDocument();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'reportId' => $this->reportId,
            'externalMessageId' => $this->externalMessageId,
            'municipality' => $this->municipality,
            'hasSeiProcess' => $this->hasSeiProcess,
            'seiProcess' => $this->seiProcess,
            'inspectionDate' => $this->inspectionDate,
            'responsiblePerson' => $this->responsiblePerson,
            'documentPath' => $this->documentPath,
            'documentName' => $this->documentName,
            'documentMimeType' => $this->documentMimeType,
            'documentSizeBytes' => $this->documentSizeBytes,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        $reportId = trim((string) ($payload['reportId'] ?? ''));
        $externalMessageId = trim((string) ($payload['externalMessageId'] ?? ''));

        if ($reportId === '' || $externalMessageId === '') {
            throw new InvalidArgumentException('O rascunho do relatório possui identificadores inválidos.');
        }

        return new self(
            reportId: $reportId,
            externalMessageId: $externalMessageId,
            municipality: self::nullableString($payload['municipality'] ?? null),
            hasSeiProcess: is_bool($payload['hasSeiProcess'] ?? null) ? $payload['hasSeiProcess'] : null,
            seiProcess: self::nullableString($payload['seiProcess'] ?? null),
            inspectionDate: self::nullableString($payload['inspectionDate'] ?? null),
            responsiblePerson: self::nullableString($payload['responsiblePerson'] ?? null),
            documentPath: self::nullableString($payload['documentPath'] ?? null),
            documentName: self::nullableString($payload['documentName'] ?? null),
            documentMimeType: self::nullableString($payload['documentMimeType'] ?? null),
            documentSizeBytes: isset($payload['documentSizeBytes']) ? (int) $payload['documentSizeBytes'] : null,
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return trim($value);
    }
}
