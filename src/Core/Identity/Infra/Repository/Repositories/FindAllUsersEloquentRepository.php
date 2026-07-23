<?php

declare(strict_types=1);

namespace App\Core\Identity\Infra\Repository\Repositories;

use App\Core\Identity\Domain\Entity\UserEntity;
use App\Models\User;

class FindAllUsersEloquentRepository
{
    /**
     * @return list<UserEntity>
     */
    public function all(): array
    {
        return User::query()
            ->orderBy('id')
            ->get()
            ->map(fn (User $model): UserEntity => UserEntity::fromModel($model))
            ->all();
    }
}
