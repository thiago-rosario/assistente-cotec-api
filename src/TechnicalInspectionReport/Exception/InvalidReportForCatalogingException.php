<?php

declare(strict_types=1);

namespace App\TechnicalInspectionReport\Exception;

use App\TechnicalInspectionReport\Enum\TechnicalInspectionReportExceptionCodeEnum;
use Throwable;

class InvalidReportForCatalogingException extends \RuntimeException
{
    public function __construct(
        string $message = 'O relatório precisa possuir um documento para ser catalogado.',
        int $code = TechnicalInspectionReportExceptionCodeEnum::InvalidReportForCataloging->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
