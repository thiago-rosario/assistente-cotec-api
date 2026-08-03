<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Exception;

use App\TechnicalInspectionReport\Enum\TechnicalInspectionReportExceptionCodeEnum;
use Throwable;

class InvalidExternalMessageIdException extends InvalidTechnicalInspectionReportValueException
{
    public function __construct(
        string $message = 'O identificador externo da mensagem é obrigatório.',
        int $code = TechnicalInspectionReportExceptionCodeEnum::InvalidExternalMessageId->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
