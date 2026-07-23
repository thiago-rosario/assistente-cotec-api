<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Interface\Usecase;

use App\Core\Identity\Application\DTO\TemporaryAuthorizationOutputDTO;
use App\Core\Identity\Application\DTO\ValidateTemporaryAuthorizationInputDTO;

interface ValidateTemporaryAuthorizationUsecaseInterface
{
    public function __invoke(ValidateTemporaryAuthorizationInputDTO $input): TemporaryAuthorizationOutputDTO;
}
