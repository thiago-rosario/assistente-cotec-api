<?php

declare(strict_types=1);

namespace App\Core\Identity\Infra\Repository\Repositories;

use App\Core\Identity\Domain\Entity\UserEntity;
use App\Core\Identity\Domain\Validation\UserDomainValidation;
use App\Models\User;

class UpdateUserEloquentRepository
{
    public function update(UserEntity $user): UserEntity
    {
        UserDomainValidation::validateId((string) $user->id);

        $model = User::query()->findOrFail($user->id);

        $model->fill([
            'name' => $user->name,
            'email' => (string) $user->login,
        ]);

        $model->save();

        return UserEntity::fromModel($model->refresh());
    }
}
