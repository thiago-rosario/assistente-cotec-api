<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Exception;

use App\TechnicalInspectionReport\Enum\TechnicalInspectionReportExceptionCodeEnum;
use Throwable;

class InvalidTechnicalInspectionReportFileException extends InvalidTechnicalInspectionReportValueException
{
    public function __construct(
        string $message = 'Os metadados do documento do relatório de vistoria técnica são inválidos.',
        int $code = TechnicalInspectionReportExceptionCodeEnum::InvalidFile->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
