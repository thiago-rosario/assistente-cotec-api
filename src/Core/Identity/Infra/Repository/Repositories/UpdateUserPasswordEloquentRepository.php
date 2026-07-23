<?php

declare(strict_types=1);

namespace App\Core\Identity\Infra\Repository\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUserPasswordEloquentRepository
{
    public function updatePassword(string $id, string $plainPassword): bool
    {
        $model = User::query()->find($id);

        if ($model === null) {
            return false;
        }

        $model->forceFill([
            'password' => Hash::make($plainPassword),
        ]);

        return $model->save();
    }
}
