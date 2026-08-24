<?php

declare(strict_types=1);

namespace App\Contract\Exception;

use App\Contract\Enum\ContractCodeExceptionEnum;
use RuntimeException;
use Throwable;

class InvalidContractAdjustmentsSearchTypeException extends RuntimeException
{
    public function __construct(
        string $message = 'Os ajustes contratuais só podem ser pesquisados por município ou número do contrato.',
        int $code = ContractCodeExceptionEnum::INVALID_ADJUSTMENTS_SEARCH_TYPE->value,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
