<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Interface\Usecase;

use App\Core\Identity\Application\DTO\ConsumeTemporaryAuthorizationInputDTO;
use App\Core\Identity\Application\DTO\TemporaryAuthorizationOutputDTO;

interface ConsumeTemporaryAuthorizationUsecaseInterface
{
    public function __invoke(ConsumeTemporaryAuthorizationInputDTO $input): TemporaryAuthorizationOutputDTO;
}
