<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Interface\Usecase;

use App\Core\Identity\Application\DTO\CancelTemporaryAuthorizationInputDTO;
use App\Core\Identity\Application\DTO\TemporaryAuthorizationOutputDTO;

interface CancelTemporaryAuthorizationUsecaseInterface
{
    public function __invoke(CancelTemporaryAuthorizationInputDTO $input): TemporaryAuthorizationOutputDTO;
}
