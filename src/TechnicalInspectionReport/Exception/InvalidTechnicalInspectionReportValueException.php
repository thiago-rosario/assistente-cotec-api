<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Exception;

use App\TechnicalInspectionReport\Enum\TechnicalInspectionReportExceptionCodeEnum;
use RuntimeException;
use Throwable;

class InvalidTechnicalInspectionReportValueException extends RuntimeException
{
    public function __construct(
        string $message = 'O valor informado para o relatório de vistoria técnica é inválido.',
        int $code = TechnicalInspectionReportExceptionCodeEnum::InvalidValue->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
