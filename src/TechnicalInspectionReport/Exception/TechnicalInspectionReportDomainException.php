<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Exception;

use App\TechnicalInspectionReport\Enum\TechnicalInspectionReportExceptionCodeEnum;
use RuntimeException;
use Throwable;

class TechnicalInspectionReportDomainException extends RuntimeException
{
    public function __construct(
        string $message = 'Ocorreu uma violação no domínio do relatório de vistoria técnica.',
        int $code = TechnicalInspectionReportExceptionCodeEnum::Domain->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
