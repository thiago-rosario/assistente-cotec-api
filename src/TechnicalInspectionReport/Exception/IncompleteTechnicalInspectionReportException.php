<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Exception;

use App\TechnicalInspectionReport\Enum\TechnicalInspectionReportExceptionCodeEnum;
use Throwable;

class IncompleteTechnicalInspectionReportException extends \RuntimeException
{
    public function __construct(
        string $message = 'O relatório de vistoria técnica não possui todos os dados obrigatórios.',
        int $code = TechnicalInspectionReportExceptionCodeEnum::IncompleteReport->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
