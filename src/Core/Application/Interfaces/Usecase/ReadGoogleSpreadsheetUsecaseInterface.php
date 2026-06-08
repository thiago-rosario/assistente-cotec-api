<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Usecase;

use App\Core\Application\DTO\ReadGoogleSpreadsheetInputDTO;
use App\Core\Application\DTO\ReadGoogleSpreadsheetOutputDTO;

interface ReadGoogleSpreadsheetUsecaseInterface
{
    public function __invoke(ReadGoogleSpreadsheetInputDTO $input): ReadGoogleSpreadsheetOutputDTO;
}
