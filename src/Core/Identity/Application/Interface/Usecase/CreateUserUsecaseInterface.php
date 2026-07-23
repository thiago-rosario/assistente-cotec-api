<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Interface\Usecase;

use App\Core\Identity\Application\DTO\CreateUserInputDTO;
use App\Core\Identity\Application\DTO\CreateUserOutputDTO;

interface CreateUserUsecaseInterface
{
    public function __invoke(CreateUserInputDTO $input): CreateUserOutputDTO;
}
