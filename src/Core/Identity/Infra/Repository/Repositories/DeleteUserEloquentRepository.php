<?php

declare(strict_types=1);

namespace App\Core\Identity\Infra\Repository\Repositories;

use App\Models\User;

class DeleteUserEloquentRepository
{
    public function delete(string $id): bool
    {
        $model = User::query()->find($id);

        return $model !== null && $model->delete() === true;
    }
}
