<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\Interfaces\Usecase;

use App\BuildPanel\Application\DTO\ReadGoogleSpreadsheetInputDTO;
use App\BuildPanel\Application\DTO\ReadGoogleSpreadsheetOutputDTO;

interface ReadGoogleSpreadsheetUsecaseInterface
{
    public function __invoke(ReadGoogleSpreadsheetInputDTO $input): ReadGoogleSpreadsheetOutputDTO;
}
