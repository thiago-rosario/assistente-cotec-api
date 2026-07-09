<?php

declare(strict_types=1);

namespace App\BuildPanel\Exception;

use App\BuildPanel\Enum\CodeExceptionEnum;
use RuntimeException;
use Throwable;

class GoogleSpreadsheetIdRequiredException extends RuntimeException
{
    public function __construct(
        string $message = 'O identificador da planilha Google deve ser informado.',
        int $code = CodeExceptionEnum::GoogleSpreadsheetIdRequired->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
