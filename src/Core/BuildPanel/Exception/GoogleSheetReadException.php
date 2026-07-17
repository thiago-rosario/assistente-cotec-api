<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Exception;

use App\Core\BuildPanel\Enum\BuildPanelCodeExceptionEnum;
use RuntimeException;
use Throwable;

class GoogleSheetReadException extends RuntimeException
{
    /**
     * @param  array{gid: int, name: string}|null  $sheet
     */
    public function __construct(
        string $message = 'Falha ao ler os dados da planilha Google.',
        int $code = BuildPanelCodeExceptionEnum::GoogleSheetRead->value,
        ?Throwable $previous = null,
        public readonly ?string $spreadsheetId = null,
        public readonly ?array $sheet = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
