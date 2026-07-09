<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\Interfaces\Usecase;

use App\BuildPanel\Application\DTO\SearchGoogleSheetInputDTO;
use App\BuildPanel\Application\DTO\SearchGoogleSheetOutputDTO;

interface SearchGoogleSheetUsecaseInterface
{
    public function __invoke(SearchGoogleSheetInputDTO $input): SearchGoogleSheetOutputDTO;
}
