<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Exception;

use App\TechnicalInspectionReport\Enum\TechnicalInspectionReportExceptionCodeEnum;
use RuntimeException;
use Throwable;

class InvalidTechnicalInspectionReportStateTransitionException extends RuntimeException
{
    public function __construct(
        string $message = 'A transição de estado do relatório de vistoria técnica é inválida.',
        int $code = TechnicalInspectionReportExceptionCodeEnum::InvalidStateTransition->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
