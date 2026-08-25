<?php

declare(strict_types=1);

namespace App\Contract\Enum;

enum ContractCodeExceptionEnum: int
{
    case SEI_PROCESS_IS_EMPTY = 3001;
    case INVALID_ADJUSTMENTS_SEARCH_TYPE = 3002;
}
