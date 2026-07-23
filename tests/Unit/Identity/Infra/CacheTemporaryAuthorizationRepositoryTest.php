<?php

use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;
use App\Core\Identity\Domain\Entity\UserEntity;
use App\Core\Identity\Domain\Repository\TemporaryAuthorizationRepositoryInterface;
use App\Core\Identity\Domain\ValueObject\AuthorizationContext;
use App\Core\Identity\Enum\ProtectedActionEnum;
use App\Core\Identity\Enum\TemporaryAuthorizationStatusEnum;
use App\Core\Identity\Infra\Repository\Cache\CacheTemporaryAuthorizationRepository;
use App\Core\Identity\Infra\Repository\Cache\TemporaryAuthorizationCacheKeyResolver;
use App\Core\Identity\Infra\Repository\Cache\TemporaryAuthorizationRetentionResolver;
use App\Core\Identity\Infra\Repository\Cache\TemporaryAuthorizationStateMapper;
use App\Core\Identity\Infra\Repository\Gateway\TemporaryAuthorizationGatewayRepository;
use App\Core\Identity\Infra\Repository\Repositories\FindActiveTemporaryAuthorizationByContextCacheRepository;
use App\Core\Identity\Infra\Repository\Repositories\FindTemporaryAuthorizationByIdCacheRepository;
use App\Core\Identity\Infra\Repository\Repositories\SaveTemporaryAuthorizationCacheRepository;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as CacheRepository;

it('stores temporary authorizations and finds the active one by context and action', function (): void {
    $repository = identityInfraTemporaryAuthorizationRepository();
    $context = identityInfraAuthorizationContext();
    $issuedAt = new DateTimeImmutable('+1 hour');
    $authorization = TemporaryAuthorizationEntity::start(
        context: $context,
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
        issuedAt: $issuedAt,
        authorizationId: 'authorization-1',
    );

    $repository->save($authorization);

    $stored = $repository->findById('authorization-1');
    $active = $repository->findActiveByContext(
        context: $context,
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
        now: $issuedAt->modify('+1 minute'),
    );

    expect($repository)->toBeInstanceOf(TemporaryAuthorizationRepositoryInterface::class)
        ->and($stored)->toBeInstanceOf(TemporaryAuthorizationEntity::class)
        ->and($stored?->authorizationId)->toBe('authorization-1')
        ->and($stored?->status)->toBe(TemporaryAuthorizationStatusEnum::PendingCredentials)
        ->and($active)->toBeInstanceOf(TemporaryAuthorizationEntity::class)
        ->and($active?->authorizationId)->toBe('authorization-1')
        ->and($repository->findActiveByContext(
            context: AuthorizationContext::forWhatsappConversation('5571888888888', 'conversation-2'),
            protectedAction: ProtectedActionEnum::TravelReportSubmission,
        ))->toBeNull()
        ->and($repository->findActiveByContext(
            context: $context,
            protectedAction: ProtectedActionEnum::BuildPanelConsultation,
        ))->toBeNull();
});

it('keeps stale non terminal authorizations indexed so the application can expire them', function (): void {
    $repository = identityInfraTemporaryAuthorizationRepository();
    $context = identityInfraAuthorizationContext();
    $issuedAt = new DateTimeImmutable('+1 hour');
    $expiredAt = $issuedAt->modify('+2 minutes');
    $authorization = TemporaryAuthorizationEntity::start(
        context: $context,
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
        timeToLive: new DateInterval('PT1M'),
        issuedAt: $issuedAt,
        authorizationId: 'authorization-stale',
    );

    $repository->save($authorization);

    $active = $repository->findActiveByContext(
        context: $context,
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
        now: $expiredAt,
    );

    expect($active)->toBeInstanceOf(TemporaryAuthorizationEntity::class)
        ->and($active?->authorizationId)->toBe('authorization-stale')
        ->and($active?->hasExpired($expiredAt))->toBeTrue();
});

it('removes terminal authorizations from the active context index while keeping lookup by id', function (): void {
    $repository = identityInfraTemporaryAuthorizationRepository();
    $context = identityInfraAuthorizationContext();
    $issuedAt = new DateTimeImmutable('+1 hour');
    $authorizedAt = $issuedAt->modify('+1 minute');
    $revokedAt = $issuedAt->modify('+2 minutes');
    $authorization = TemporaryAuthorizationEntity::start(
        context: $context,
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
        issuedAt: $issuedAt,
        authorizationId: 'authorization-1',
    )->authorize(
        user: UserEntity::identified(
            id: 'user-1',
            name: 'Thiago Souza',
            login: 'thiago@example.com',
        ),
        now: $authorizedAt,
    );

    $repository->save($authorization);
    $repository->save($authorization->revoke($revokedAt));

    expect($repository->findById('authorization-1')?->status)->toBe(TemporaryAuthorizationStatusEnum::Revoked)
        ->and($repository->findActiveByContext(
            context: $context,
            protectedAction: ProtectedActionEnum::TravelReportSubmission,
        ))->toBeNull();
});

function identityInfraTemporaryAuthorizationRepository(): TemporaryAuthorizationGatewayRepository
{
    $cacheRepository = new CacheTemporaryAuthorizationRepository(
        cache: new CacheRepository(new ArrayStore),
        keyResolver: new TemporaryAuthorizationCacheKeyResolver,
    );

    $findByIdRepository = new FindTemporaryAuthorizationByIdCacheRepository(
        cacheRepository: $cacheRepository,
        stateMapper: new TemporaryAuthorizationStateMapper,
    );

    return new TemporaryAuthorizationGatewayRepository(
        saveRepository: new SaveTemporaryAuthorizationCacheRepository(
            cacheRepository: $cacheRepository,
            retentionResolver: new TemporaryAuthorizationRetentionResolver,
            stateMapper: new TemporaryAuthorizationStateMapper,
        ),
        findByIdRepository: $findByIdRepository,
        findActiveByContextRepository: new FindActiveTemporaryAuthorizationByContextCacheRepository(
            cacheRepository: $cacheRepository,
            findByIdRepository: $findByIdRepository,
        ),
    );
}

function identityInfraAuthorizationContext(): AuthorizationContext
{
    return AuthorizationContext::forWhatsappConversation(
        whatsappNumber: '5571999999999',
        conversationId: 'conversation-1',
    );
}
