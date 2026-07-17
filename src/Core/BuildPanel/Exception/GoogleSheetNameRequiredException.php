<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Exception;

use App\Core\BuildPanel\Enum\BuildPanelCodeExceptionEnum;
use RuntimeException;
use Throwable;

class GoogleSheetNameRequiredException extends RuntimeException
{
    public function __construct(
        string $message = 'O nome da aba da planilha Google deve ser informado.',
        int $code = BuildPanelCodeExceptionEnum::GoogleSheetNameRequired->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
