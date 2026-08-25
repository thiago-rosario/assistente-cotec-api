<?php

declare(strict_types=1);

namespace App\Contract\Application\Interfaces\Usecase;

use App\Contract\Application\DTO\ContractValueAdditivesOutputDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;

interface FindContractValueAdditivesUsecaseInterface
{
    public function __invoke(SearchContractInputDTO $input): ContractValueAdditivesOutputDTO;
}
