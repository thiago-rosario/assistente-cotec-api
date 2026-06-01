<?php

declare(strict_types=1);

namespace App\Core\Exception;

use App\Core\Enum\CodeExceptionEnum;
use RuntimeException;
use Throwable;

class GoogleSheetGidInvalidException extends RuntimeException
{
    public function __construct(
        string $message = 'O gid da aba da planilha Google deve ser maior que zero.',
        int $code = CodeExceptionEnum::GoogleSheetGidInvalid->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
