<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Exception;

use App\Core\TravelReport\Enum\TravelReportCodeExceptionEnum;
use RuntimeException;
use Throwable;

class FilePathRequiredException extends RuntimeException
{
    public function __construct(
        string $message = 'O caminho do arquivo é obrigatório.',
        int $code = TravelReportCodeExceptionEnum::FilePathRequired->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
