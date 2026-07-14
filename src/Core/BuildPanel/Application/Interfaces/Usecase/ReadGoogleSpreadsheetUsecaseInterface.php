<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Application\Interfaces\Usecase;

use App\Core\BuildPanel\Application\DTO\ReadGoogleSpreadsheetInputDTO;
use App\Core\BuildPanel\Application\DTO\ReadGoogleSpreadsheetOutputDTO;

interface ReadGoogleSpreadsheetUsecaseInterface
{
    public function __invoke(ReadGoogleSpreadsheetInputDTO $input): ReadGoogleSpreadsheetOutputDTO;
}
