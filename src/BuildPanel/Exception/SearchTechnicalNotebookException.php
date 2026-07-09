<?php

declare(strict_types=1);

namespace App\BuildPanel\Exception;

use App\BuildPanel\Enum\CodeExceptionEnum;
use RuntimeException;
use Throwable;

class SearchTechnicalNotebookException extends RuntimeException
{
    public function __construct(
        string $message = 'Erro ao buscar informações na aba do caderno técnico',
        int $code = CodeExceptionEnum::SearchTechnicalNotebookError->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
