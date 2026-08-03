<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Exception;

use App\TechnicalInspectionReport\Enum\TechnicalInspectionReportExceptionCodeEnum;
use Throwable;

class InvalidResponsiblePersonException extends InvalidTechnicalInspectionReportValueException
{
    public function __construct(
        string $message = 'O responsável pelo relatório é obrigatório.',
        int $code = TechnicalInspectionReportExceptionCodeEnum::InvalidResponsiblePerson->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
