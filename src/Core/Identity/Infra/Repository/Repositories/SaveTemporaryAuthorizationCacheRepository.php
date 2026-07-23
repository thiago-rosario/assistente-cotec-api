<?php

declare(strict_types=1);

namespace App\Core\Identity\Infra\Repository\Repositories;

use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;
use App\Core\Identity\Domain\Policy\TemporaryAuthorizationStatusPolicy;
use App\Core\Identity\Infra\Repository\Cache\CacheTemporaryAuthorizationRepository;
use App\Core\Identity\Infra\Repository\Cache\TemporaryAuthorizationRetentionResolver;
use App\Core\Identity\Infra\Repository\Cache\TemporaryAuthorizationStateMapper;

final readonly class SaveTemporaryAuthorizationCacheRepository
{
    public function __construct(
        private CacheTemporaryAuthorizationRepository $cacheRepository,
        private TemporaryAuthorizationRetentionResolver $retentionResolver,
        private TemporaryAuthorizationStateMapper $stateMapper,
    ) {}

    public function save(TemporaryAuthorizationEntity $authorization): TemporaryAuthorizationEntity
    {
        $retentionUntil = $this->retentionResolver->resolve($authorization);

        $this->cacheRepository->putAuthorization(
            authorization: $authorization,
            state: $this->stateMapper->toState($authorization),
            retentionUntil: $retentionUntil,
        );

        if (TemporaryAuthorizationStatusPolicy::isTerminal($authorization->status)) {
            $this->cacheRepository->forgetContextIndexForAuthorization($authorization);

            return $authorization;
        }

        $this->cacheRepository->putContextIndex($authorization, $retentionUntil);

        return $authorization;
    }
}
