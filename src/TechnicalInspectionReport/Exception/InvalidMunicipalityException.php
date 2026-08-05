<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Exception;

use App\TechnicalInspectionReport\Enum\TechnicalInspectionReportExceptionCodeEnum;
use Throwable;

class InvalidMunicipalityException extends \RuntimeException
{
    public function __construct(
        string $message = 'O município da vistoria técnica é obrigatório.',
        int $code = TechnicalInspectionReportExceptionCodeEnum::InvalidMunicipality->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
