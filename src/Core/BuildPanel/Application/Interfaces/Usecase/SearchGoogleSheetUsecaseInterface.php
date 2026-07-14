<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Application\Interfaces\Usecase;

use App\Core\BuildPanel\Application\DTO\SearchGoogleSheetInputDTO;
use App\Core\BuildPanel\Application\DTO\SearchGoogleSheetOutputDTO;

interface SearchGoogleSheetUsecaseInterface
{
    public function __invoke(SearchGoogleSheetInputDTO $input): SearchGoogleSheetOutputDTO;
}
