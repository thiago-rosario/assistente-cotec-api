<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Exception;

use App\TechnicalInspectionReport\Enum\TechnicalInspectionReportExceptionCodeEnum;
use Throwable;

class InvalidSeiProcessException extends InvalidTechnicalInspectionReportValueException
{
    public function __construct(
        string $message = 'O processo SEI informado possui formato inválido.',
        int $code = TechnicalInspectionReportExceptionCodeEnum::InvalidSeiProcess->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
