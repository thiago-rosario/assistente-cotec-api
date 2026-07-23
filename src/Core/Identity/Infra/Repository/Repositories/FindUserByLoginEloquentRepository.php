<?php

declare(strict_types=1);

namespace App\Core\Identity\Infra\Repository\Repositories;

use App\Core\Identity\Domain\Entity\UserEntity;
use App\Core\Identity\Domain\ValueObject\Login;
use App\Models\User;

class FindUserByLoginEloquentRepository
{
    public function findByLogin(Login $login): ?UserEntity
    {
        $model = User::query()
            ->where('email', (string) $login)
            ->first();

        if ($model === null) {
            return null;
        }

        return UserEntity::fromModel($model);
    }
}
