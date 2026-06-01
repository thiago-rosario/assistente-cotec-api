<?php

declare(strict_types=1);

namespace App\Core\Exception;

use App\Core\Enum\CodeExceptionEnum;
use RuntimeException;
use Throwable;

class GoogleSheetNameRequiredException extends RuntimeException
{
    public function __construct(
        string $message = 'O nome da aba da planilha Google deve ser informado.',
        int $code = CodeExceptionEnum::GoogleSheetNameRequired->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
