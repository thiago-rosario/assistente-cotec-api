<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\DTO;

readonly class CreateUserInputDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $passwordConfirmation,
    ) {}
}
