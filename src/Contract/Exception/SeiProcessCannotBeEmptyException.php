<?php

declare(strict_types=1);

namespace App\Contract\Exception;

use App\Contract\Enum\ContractCodeExceptionEnum;
use RuntimeException;
use Throwable;

class SeiProcessCannotBeEmptyException extends RuntimeException
{
    public function __construct(
        string $message = 'O processo SEI não pode estar vazio.',
        int $code = ContractCodeExceptionEnum::SEI_PROCESS_IS_EMPTY->value,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
