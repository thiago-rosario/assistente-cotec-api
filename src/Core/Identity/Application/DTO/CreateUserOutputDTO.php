<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\DTO;

use DateTimeImmutable;

readonly class CreateUserOutputDTO
{
    public function __construct(
        public ?string $id,
        public string $name,
        public string $email,
        public DateTimeImmutable $createdAt,
    ) {}
}
