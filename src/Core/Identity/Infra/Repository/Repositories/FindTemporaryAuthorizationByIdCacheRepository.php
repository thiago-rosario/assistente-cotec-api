<?php

declare(strict_types=1);

namespace App\Core\Identity\Infra\Repository\Repositories;

use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;
use App\Core\Identity\Infra\Repository\Cache\CacheTemporaryAuthorizationRepository;
use App\Core\Identity\Infra\Repository\Cache\TemporaryAuthorizationStateMapper;

final readonly class FindTemporaryAuthorizationByIdCacheRepository
{
    public function __construct(
        private CacheTemporaryAuthorizationRepository $cacheRepository,
        private TemporaryAuthorizationStateMapper $stateMapper,
    ) {}

    public function findById(string $authorizationId): ?TemporaryAuthorizationEntity
    {
        return $this->stateMapper->fromState(
            $this->cacheRepository->getAuthorizationState($authorizationId),
        );
    }
}
