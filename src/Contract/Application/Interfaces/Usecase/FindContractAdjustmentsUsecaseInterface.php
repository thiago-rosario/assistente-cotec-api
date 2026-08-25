<?php

declare(strict_types=1);

namespace App\Contract\Application\Interfaces\Usecase;

use App\Contract\Application\DTO\ContractAdjustmentsOutputDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;

interface FindContractAdjustmentsUsecaseInterface
{
    public function __invoke(SearchContractInputDTO $input): ContractAdjustmentsOutputDTO;
}
