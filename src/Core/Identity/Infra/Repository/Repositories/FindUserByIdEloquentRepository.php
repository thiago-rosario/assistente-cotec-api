<?php

declare(strict_types=1);

namespace App\Core\Identity\Infra\Repository\Repositories;

use App\Core\Identity\Domain\Entity\UserEntity;
use App\Models\User;

class FindUserByIdEloquentRepository
{
    public function findById(string $id): ?UserEntity
    {
        $model = User::query()->find($id);

        if ($model === null) {
            return null;
        }

        return UserEntity::fromModel($model);
    }
}
