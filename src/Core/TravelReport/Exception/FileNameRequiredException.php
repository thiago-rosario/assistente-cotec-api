<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Exception;

use App\Core\TravelReport\Enum\TravelReportCodeExceptionEnum;
use RuntimeException;
use Throwable;

class FileNameRequiredException extends RuntimeException
{
    public function __construct(
        string $message = 'O nome do arquivo do relatório é obrigatório.',
        int $code = TravelReportCodeExceptionEnum::FileNameRequired->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
