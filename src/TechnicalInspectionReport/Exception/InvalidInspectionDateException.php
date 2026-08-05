<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Exception;

use App\TechnicalInspectionReport\Enum\TechnicalInspectionReportExceptionCodeEnum;
use Throwable;

class InvalidInspectionDateException extends \RuntimeException
{
    public function __construct(
        string $message = 'A data da vistoria técnica deve ser uma data válida no formato dd/mm/aaaa.',
        int $code = TechnicalInspectionReportExceptionCodeEnum::InvalidInspectionDate->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
