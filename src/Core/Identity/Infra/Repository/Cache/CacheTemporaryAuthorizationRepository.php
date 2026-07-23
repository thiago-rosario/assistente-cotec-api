<?php

declare(strict_types=1);

namespace App\Core\Identity\Infra\Repository\Cache;

use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;
use App\Core\Identity\Domain\ValueObject\AuthorizationContext;
use App\Core\Identity\Enum\ProtectedActionEnum;
use DateTimeImmutable;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final readonly class CacheTemporaryAuthorizationRepository
{
    public function __construct(
        private CacheRepository $cache,
        private TemporaryAuthorizationCacheKeyResolver $keyResolver,
    ) {}

    /**
     * @param  array<string, mixed>  $state
     */
    public function putAuthorization(
        TemporaryAuthorizationEntity $authorization,
        array $state,
        DateTimeImmutable $retentionUntil,
    ): void {
        $this->cache->put(
            key: $this->keyResolver->authorizationKey($authorization->authorizationId),
            value: $state,
            ttl: $retentionUntil,
        );
    }

    public function getAuthorizationState(string $authorizationId): mixed
    {
        return $this->cache->get($this->keyResolver->authorizationKey($authorizationId));
    }

    public function putContextIndex(
        TemporaryAuthorizationEntity $authorization,
        DateTimeImmutable $retentionUntil,
    ): void {
        $this->cache->put(
            key: $this->keyResolver->contextKey($authorization->context, $authorization->protectedAction),
            value: $authorization->authorizationId,
            ttl: $retentionUntil,
        );
    }

    public function getContextAuthorizationId(
        AuthorizationContext $context,
        ProtectedActionEnum $protectedAction,
    ): ?string {
        $authorizationId = $this->cache->get($this->keyResolver->contextKey($context, $protectedAction));

        if (! is_string($authorizationId)) {
            return null;
        }

        $authorizationId = trim($authorizationId);

        return $authorizationId === '' ? null : $authorizationId;
    }

    public function forgetContextIndex(
        AuthorizationContext $context,
        ProtectedActionEnum $protectedAction,
    ): void {
        $this->cache->forget($this->keyResolver->contextKey($context, $protectedAction));
    }

    public function forgetContextIndexForAuthorization(TemporaryAuthorizationEntity $authorization): void
    {
        if ($this->getContextAuthorizationId($authorization->context, $authorization->protectedAction) === $authorization->authorizationId) {
            $this->forgetContextIndex($authorization->context, $authorization->protectedAction);
        }
    }
}
