<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Exception;

use App\Core\BuildPanel\Enum\BuildPanelCodeExceptionEnum;
use RuntimeException;
use Throwable;

class SearchTechnicalNotebookException extends RuntimeException
{
    public function __construct(
        string $message = 'Erro ao buscar informações na aba do caderno técnico',
        int $code = BuildPanelCodeExceptionEnum::SearchTechnicalNotebookError->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
