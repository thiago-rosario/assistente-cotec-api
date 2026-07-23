<?php

declare(strict_types=1);

namespace App\Core\Identity\Infra\Repository\Gateway;

use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;
use App\Core\Identity\Domain\Repository\TemporaryAuthorizationRepositoryInterface;
use App\Core\Identity\Domain\ValueObject\AuthorizationContext;
use App\Core\Identity\Enum\ProtectedActionEnum;
use App\Core\Identity\Infra\Repository\Repositories\FindActiveTemporaryAuthorizationByContextCacheRepository;
use App\Core\Identity\Infra\Repository\Repositories\FindTemporaryAuthorizationByIdCacheRepository;
use App\Core\Identity\Infra\Repository\Repositories\SaveTemporaryAuthorizationCacheRepository;
use DateTimeInterface;

final readonly class TemporaryAuthorizationGatewayRepository implements TemporaryAuthorizationRepositoryInterface
{
    public function __construct(
        private SaveTemporaryAuthorizationCacheRepository $saveRepository,
        private FindTemporaryAuthorizationByIdCacheRepository $findByIdRepository,
        private FindActiveTemporaryAuthorizationByContextCacheRepository $findActiveByContextRepository,
    ) {}

    public function save(TemporaryAuthorizationEntity $authorization): TemporaryAuthorizationEntity
    {
        return $this->saveRepository->save($authorization);
    }

    public function findById(string $authorizationId): ?TemporaryAuthorizationEntity
    {
        return $this->findByIdRepository->findById($authorizationId);
    }

    public function findActiveByContext(
        AuthorizationContext $context,
        ProtectedActionEnum $protectedAction,
        DateTimeInterface|string|null $now = null,
    ): ?TemporaryAuthorizationEntity {
        return $this->findActiveByContextRepository->findActiveByContext(
            context: $context,
            protectedAction: $protectedAction,
            now: $now,
        );
    }
}
