<?php

declare(strict_types=1);

namespace App\Core\Exception;

use App\Core\Enum\CodeExceptionEnum;

class SearchConstructionDemandException extends \RuntimeException
{
    public function __construct(
        string $message = 'Erro ao buscar informações na aba de Demanda de Construção',
        int $code = CodeExceptionEnum::SearchConstructionDemandError->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
