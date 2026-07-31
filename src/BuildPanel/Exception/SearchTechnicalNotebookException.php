<?php

declare(strict_types=1);

namespace App\BuildPanel\Exception;

use Throwable;

class SearchTechnicalNotebookException extends \RuntimeException
{
    private const int SearchTechnicalNotebookErrorCode = 1008;

    public function __construct(
        string $message = 'Erro ao buscar informações na aba do caderno técnico',
        int $code = self::SearchTechnicalNotebookErrorCode,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
