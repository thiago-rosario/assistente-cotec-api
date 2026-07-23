<?php

declare(strict_types=1);

namespace App\Core\Identity\Infra\Repository\Repositories;

use App\Core\Identity\Domain\Entity\UserEntity;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUserEloquentRepository
{
    public function insert(UserEntity $user, string $plainPassword): UserEntity
    {
        $model = User::query()->create([
            ...$user->toPersistenceArray(),
            'password' => Hash::make($plainPassword),
        ]);

        return UserEntity::fromModel($model);
    }
}
