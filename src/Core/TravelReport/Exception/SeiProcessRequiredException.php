<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Exception;

use App\Core\TravelReport\Enum\TravelReportCodeExceptionEnum;
use RuntimeException;
use Throwable;

class SeiProcessRequiredException extends RuntimeException
{
    public function __construct(
        string $message = 'O processo SEI do relatório é obrigatório.',
        int $code = TravelReportCodeExceptionEnum::SeiProcessRequired->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
