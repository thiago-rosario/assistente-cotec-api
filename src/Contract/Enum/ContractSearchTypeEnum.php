<?php

declare(strict_types=1);

namespace App\Contract\Enum;

enum ContractSearchTypeEnum: string
{
    case ContractNumber = 'contract_number';
    case SeiProcess = 'sei_process';
    case Municipality = 'municipality';
    case Company = 'company';
}
