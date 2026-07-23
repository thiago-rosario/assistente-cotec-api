<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Interface\Usecase;

use App\Core\Identity\Application\DTO\StartTemporaryAuthorizationInputDTO;
use App\Core\Identity\Application\DTO\TemporaryAuthorizationOutputDTO;

interface StartTemporaryAuthorizationUsecaseInterface
{
    public function __invoke(StartTemporaryAuthorizationInputDTO $input): TemporaryAuthorizationOutputDTO;
}
