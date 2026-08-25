<?php

declare(strict_types=1);

namespace App\Contract\Application\Interfaces\Usecase;

use App\Contract\Application\DTO\ContractExecutionDeadlinesOutputDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;

interface FindContractExecutionDeadlineUsecaseInterface
{
    public function __invoke(SearchContractInputDTO $input): ContractExecutionDeadlinesOutputDTO;
}
