<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Usecase;

use App\Core\Application\DTO\SearchConstructionDemandInputDTO;
use App\Core\Application\DTO\SearchConstructionDemandOutputDTO;

interface SearchConstructionDemandUsecaseInterface
{
    public function __invoke(SearchConstructionDemandInputDTO $input): SearchConstructionDemandOutputDTO;
}
