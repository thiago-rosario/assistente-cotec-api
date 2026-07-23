<?php

declare(strict_types=1);

namespace App\Core\Identity\Infra\Service;

use App\Core\Identity\Domain\Entity\UserEntity;
use App\Core\Identity\Domain\Service\CredentialsVerifierInterface;
use App\Core\Identity\Domain\ValueObject\Login;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class EloquentCredentialsVerifierService implements CredentialsVerifierInterface
{
    public function verify(Login $login, #[\SensitiveParameter] string $plainPassword): ?UserEntity
    {
        $model = User::query()
            ->where('email', (string) $login)
            ->first();

        if ($model === null || ! Hash::check($plainPassword, (string) $model->password)) {
            return null;
        }

        return UserEntity::fromModel($model);
    }
}
