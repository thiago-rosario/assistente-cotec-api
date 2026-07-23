<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Interface\Service;

use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;
use App\Core\Identity\Domain\ValueObject\AuthorizationContext;
use App\Core\Identity\Enum\ProtectedActionEnum;

interface TemporaryAuthorizationGuardServiceInterface
{
    public function assertContextMatches(TemporaryAuthorizationEntity $authorization, AuthorizationContext $context): void;

    public function assertProtectedActionMatches(TemporaryAuthorizationEntity $authorization, ProtectedActionEnum $protectedAction): void;

    public function assertNotExpired(TemporaryAuthorizationEntity $authorization, \DateTimeImmutable $now): void;

    public function assertCancellable(TemporaryAuthorizationEntity $authorization): void;

    public function assertAuthorizedForExecution(TemporaryAuthorizationEntity $authorization, \DateTimeImmutable $now): void;

    public function assertCanAuthenticate(TemporaryAuthorizationEntity $authorization, AuthorizationContext $context, ProtectedActionEnum $action, \DateTimeImmutable $now): void;

    public function assertAuthorized(TemporaryAuthorizationEntity $authorization, \DateTimeImmutable $now): void;
}
