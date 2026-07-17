<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Exception;

use App\Core\TravelReport\Enum\TravelReportCodeExceptionEnum;
use RuntimeException;
use Throwable;

class InvalidMunicipalityIdException extends RuntimeException
{
    public function __construct(
        string $message = 'O ID do município é inválido.',
        int $code = TravelReportCodeExceptionEnum::InvalidMunicipalityId->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
