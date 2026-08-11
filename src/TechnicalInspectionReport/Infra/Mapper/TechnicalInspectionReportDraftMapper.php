<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Infra\Mapper;

use App\TechnicalInspectionReport\Application\DTO\TechnicalInspectionReportDraftDTO;
use App\TechnicalInspectionReport\Application\Interfaces\Mapper\TechnicalInspectionReportDraftMapperInterface;
use InvalidArgumentException;

final class TechnicalInspectionReportDraftMapper implements TechnicalInspectionReportDraftMapperInterface
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(TechnicalInspectionReportDraftDTO $draft): array
    {
        return [
            'reportId' => $draft->reportId,
            'externalMessageId' => $draft->externalMessageId,
            'municipality' => $draft->municipality,
            'hasSeiProcess' => $draft->hasSeiProcess,
            'seiProcess' => $draft->seiProcess,
            'inspectionDate' => $draft->inspectionDate,
            'responsiblePerson' => $draft->responsiblePerson,
            'documentPath' => $draft->documentPath,
            'documentName' => $draft->documentName,
            'documentMimeType' => $draft->documentMimeType,
            'documentSizeBytes' => $draft->documentSizeBytes,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function fromArray(array $payload): TechnicalInspectionReportDraftDTO
    {
        $reportId = trim((string) ($payload['reportId'] ?? ''));
        $externalMessageId = trim((string) ($payload['externalMessageId'] ?? ''));

        if ($reportId === '' || $externalMessageId === '') {
            throw new InvalidArgumentException('O rascunho do relatório possui identificadores inválidos.');
        }

        return new TechnicalInspectionReportDraftDTO(
            reportId: $reportId,
            externalMessageId: $externalMessageId,
            municipality: $this->nullableString($payload['municipality'] ?? null),
            hasSeiProcess: is_bool($payload['hasSeiProcess'] ?? null) ? $payload['hasSeiProcess'] : null,
            seiProcess: $this->nullableString($payload['seiProcess'] ?? null),
            inspectionDate: $this->nullableString($payload['inspectionDate'] ?? null),
            responsiblePerson: $this->nullableString($payload['responsiblePerson'] ?? null),
            documentPath: $this->nullableString($payload['documentPath'] ?? null),
            documentName: $this->nullableString($payload['documentName'] ?? null),
            documentMimeType: $this->nullableString($payload['documentMimeType'] ?? null),
            documentSizeBytes: isset($payload['documentSizeBytes']) ? (int) $payload['documentSizeBytes'] : null,
        );
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return trim($value);
    }
}
