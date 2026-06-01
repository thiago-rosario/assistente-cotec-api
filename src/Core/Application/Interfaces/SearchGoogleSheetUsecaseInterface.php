<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces;

use App\Core\Application\DTO\SearchGoogleSheetInputDTO;
use App\Core\Application\DTO\SearchGoogleSheetOutputDTO;

interface SearchGoogleSheetUsecaseInterface
{
    public function __invoke(SearchGoogleSheetInputDTO $input): SearchGoogleSheetOutputDTO;
}
