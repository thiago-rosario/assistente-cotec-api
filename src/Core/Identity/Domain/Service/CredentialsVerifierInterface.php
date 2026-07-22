<?php

declare(strict_types=1);

namespace App\Core\Identity\Domain\Service;

use App\Core\Identity\Domain\Entity\UserEntity;
use App\Core\Identity\Domain\ValueObject\Login;

interface CredentialsVerifierInterface
{
    public function verify(Login $login, string $plainPassword): ?UserEntity;
}
