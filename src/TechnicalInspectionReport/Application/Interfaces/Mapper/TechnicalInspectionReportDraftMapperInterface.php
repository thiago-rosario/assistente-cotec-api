<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Application\Interfaces\Mapper;

use App\TechnicalInspectionReport\Application\DTO\TechnicalInspectionReportDraftDTO;

interface TechnicalInspectionReportDraftMapperInterface
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(TechnicalInspectionReportDraftDTO $draft): array;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function fromArray(array $payload): TechnicalInspectionReportDraftDTO;
}
