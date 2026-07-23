<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Interface\Usecase;

use App\Core\Identity\Application\DTO\AuthenticateTemporaryAuthorizationInputDTO;
use App\Core\Identity\Application\DTO\TemporaryAuthorizationOutputDTO;

interface AuthenticateTemporaryAuthorizationUsecaseInterface
{
    public function __invoke(AuthenticateTemporaryAuthorizationInputDTO $input): TemporaryAuthorizationOutputDTO;
}
