<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Exception;

use App\Core\TravelReport\Enum\TravelReportCodeExceptionEnum;
use RuntimeException;
use Throwable;

class SubmittedByUserIdRequiredException extends RuntimeException
{
    public function __construct(
        string $message = 'O usuário responsável pelo envio é obrigatório.',
        int $code = TravelReportCodeExceptionEnum::SubmittedByUserIdRequired->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
