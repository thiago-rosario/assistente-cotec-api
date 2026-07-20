<?php

declare(strict_types=1);

namespace App\Core\Identity\Domain\Repository;

use App\Core\Identity\Domain\Entity\UserEntity;
use App\Core\Identity\Domain\ValueObject\Login;

interface UserRepositoryInterface
{
    public function findById(string $id): ?UserEntity;

    public function findByLogin(Login $login): ?UserEntity;
}
