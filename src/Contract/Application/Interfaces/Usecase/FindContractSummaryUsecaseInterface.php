<?php

declare(strict_types=1);

namespace App\Contract\Application\Interfaces\Usecase;

use App\Contract\Application\DTO\FindContractSummaryOutputDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;

interface FindContractSummaryUsecaseInterface
{
    public function __invoke(SearchContractInputDTO $input): FindContractSummaryOutputDTO;
}
