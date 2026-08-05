<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Exception;

use App\TechnicalInspectionReport\Enum\TechnicalInspectionReportExceptionCodeEnum;
use Throwable;

class InvalidTechnicalInspectionReportIdException extends \RuntimeException
{
    public function __construct(
        string $message = 'O identificador do relatório de vistoria técnica é obrigatório.',
        int $code = TechnicalInspectionReportExceptionCodeEnum::InvalidReportId->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
