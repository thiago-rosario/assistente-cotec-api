<?php

declare(strict_types=1);

namespace App\Contract\Application\Interfaces\Usecase;

use App\Contract\Application\DTO\SearchContractInputDTO;
use App\Contract\Application\DTO\SearchContractOutputDTO;

interface SearchContractUsecaseInterface
{
    public function __invoke(SearchContractInputDTO $input): SearchContractOutputDTO;
}
