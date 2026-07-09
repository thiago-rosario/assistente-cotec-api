<?php

declare(strict_types=1);

namespace App\BuildPanel\Exception;

use App\BuildPanel\Enum\CodeExceptionEnum;
use RuntimeException;
use Throwable;

class GoogleSheetReadException extends RuntimeException
{
    /**
     * @param  array{gid: int, name: string}|null  $sheet
     */
    public function __construct(
        string $message = 'Falha ao ler os dados da planilha Google.',
        int $code = CodeExceptionEnum::GoogleSheetRead->value,
        ?Throwable $previous = null,
        public readonly ?string $spreadsheetId = null,
        public readonly ?array $sheet = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
