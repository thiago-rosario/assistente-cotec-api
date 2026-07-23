<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Interface\Service;

use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;
use App\Core\Identity\Domain\Entity\UserEntity;
use App\Core\Identity\Domain\ValueObject\Login;

interface TemporaryAuthorizationCredentialsAuthenticatorServiceInterface
{
    public function authenticateOrFail(TemporaryAuthorizationEntity $authorization, Login $login, #[\SensitiveParameter] string $password, \DateTimeImmutable $now): UserEntity;
}
