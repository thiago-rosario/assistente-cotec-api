<?php

declare(strict_types=1);

namespace App\Core\Identity\Domain\Repository;

use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;
use App\Core\Identity\Domain\ValueObject\AuthorizationContext;
use App\Core\Identity\Enum\ProtectedActionEnum;
use DateTimeInterface;

interface TemporaryAuthorizationRepositoryInterface
{
    public function save(TemporaryAuthorizationEntity $authorization): TemporaryAuthorizationEntity;

    public function findById(string $authorizationId): ?TemporaryAuthorizationEntity;

    public function findActiveByContext(
        AuthorizationContext $context,
        ProtectedActionEnum $protectedAction,
        DateTimeInterface|string|null $now = null,
    ): ?TemporaryAuthorizationEntity;
}
