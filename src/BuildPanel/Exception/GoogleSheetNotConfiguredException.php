<?php

declare(strict_types=1);

namespace App\BuildPanel\Exception;

use App\BuildPanel\Enum\CodeExceptionEnum;
use RuntimeException;
use Throwable;

class GoogleSheetNotConfiguredException extends RuntimeException
{
    public function __construct(
        public readonly int $sheetId,
        string $message = 'A aba informada não está configurada para consulta.',
        int $code = CodeExceptionEnum::GoogleSheetNotConfigured->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
