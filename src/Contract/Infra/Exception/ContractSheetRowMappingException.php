<?php

declare(strict_types=1);

namespace App\Contract\Infra\Exception;

use RuntimeException;
use Throwable;

class ContractSheetRowMappingException extends RuntimeException
{
    public function __construct(
        string $message = 'A estrutura da planilha de contratos não pode ser convertida.',
        ?Throwable $previous = null,
        public readonly ?string $sheet = null,
        public readonly ?int $row = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
