<?php

use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;
use App\Core\Identity\Domain\Entity\UserEntity;
use App\Core\Identity\Domain\Policy\TemporaryAuthorizationPolicy;
use App\Core\Identity\Domain\ValueObject\AuthorizationContext;
use App\Core\Identity\Enum\IdentityCodeExceptionEnum;
use App\Core\Identity\Enum\ProtectedActionEnum;
use App\Core\Identity\Enum\TemporaryAuthorizationStatusEnum;
use App\Core\Identity\Exception\AuthorizationIdRequiredException;
use App\Core\Identity\Exception\ConversationIdRequiredException;
use App\Core\Identity\Exception\InvalidAuthorizationAttemptLimitException;
use App\Core\Identity\Exception\InvalidAuthorizationExpirationException;
use App\Core\Identity\Exception\WhatsappNumberRequiredException;

it('starts a pending temporary authorization for travel report submission', function (): void {
    $issuedAt = new DateTimeImmutable('2026-07-20 08:30:00');

    $authorization = TemporaryAuthorizationEntity::start(
        context: validAuthorizationContext(),
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
        maxAttempts: 3,
        timeToLive: new DateInterval('PT5M'),
        issuedAt: $issuedAt,
        authorizationId: 'authorization-1',
    );

    expect($authorization->authorizationId)->toBe('authorization-1')
        ->and($authorization->status)->toBe(TemporaryAuthorizationStatusEnum::PendingCredentials)
        ->and($authorization->protectedAction)->toBe(ProtectedActionEnum::TravelReportSubmission)
        ->and($authorization->authorizedUserId)->toBeNull()
        ->and($authorization->failedAttempts)->toBe(0)
        ->and($authorization->remainingAttempts())->toBe(3)
        ->and($authorization->canReceiveCredentialAttempt('2026-07-20 08:34:59'))->toBeTrue()
        ->and($authorization->isAuthorized('2026-07-20 08:34:59'))->toBeFalse()
        ->and($authorization->expiresAt->format('Y-m-d H:i:s'))->toBe('2026-07-20 08:35:00');
});

it('records failed attempts until the temporary authorization reaches its limit', function (): void {
    $authorization = TemporaryAuthorizationEntity::start(
        context: validAuthorizationContext(),
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
        maxAttempts: 2,
        issuedAt: '2026-07-20 08:30:00',
        authorizationId: 'authorization-1',
    );

    $firstAttempt = $authorization->recordFailedAttempt('2026-07-20 08:31:00');
    $secondAttempt = $firstAttempt->recordFailedAttempt('2026-07-20 08:32:00');

    expect($firstAttempt->failedAttempts)->toBe(1)
        ->and($firstAttempt->status)->toBe(TemporaryAuthorizationStatusEnum::PendingCredentials)
        ->and($secondAttempt->failedAttempts)->toBe(2)
        ->and($secondAttempt->remainingAttempts())->toBe(0)
        ->and($secondAttempt->status)->toBe(TemporaryAuthorizationStatusEnum::AttemptsExceeded)
        ->and($secondAttempt->canReceiveCredentialAttempt('2026-07-20 08:32:30'))->toBeFalse()
        ->and($secondAttempt->finishedAt?->format('Y-m-d H:i:s'))->toBe('2026-07-20 08:32:00');
});

it('authorizes the protected action without storing the submitted password', function (): void {
    $context = validAuthorizationContext();
    $authorization = TemporaryAuthorizationEntity::start(
        context: $context,
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
        issuedAt: '2026-07-20 08:30:00',
        authorizationId: 'authorization-1',
    );

    $authorized = $authorization->authorize(
        user: UserEntity::identified(
            id: 'user-1',
            name: 'Thiago Souza',
            login: 'thiago@example.com',
        ),
        now: '2026-07-20 08:31:00',
    );

    expect($authorized->status)->toBe(TemporaryAuthorizationStatusEnum::Authorized)
        ->and($authorized->authorizedUserId)->toBe('user-1')
        ->and($authorized->authorizedAt?->format('Y-m-d H:i:s'))->toBe('2026-07-20 08:31:00')
        ->and($authorized->isAuthorized('2026-07-20 08:35:00'))->toBeTrue()
        ->and($authorized->allows(ProtectedActionEnum::TravelReportSubmission, $context, '2026-07-20 08:35:00'))->toBeTrue()
        ->and(array_key_exists('password', $authorized->toStateArray()))->toBeFalse();
});

it('does not authorize an expired temporary authorization', function (): void {
    $authorization = TemporaryAuthorizationEntity::start(
        context: validAuthorizationContext(),
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
        timeToLive: new DateInterval('PT1M'),
        issuedAt: '2026-07-20 08:30:00',
        authorizationId: 'authorization-1',
    );

    $authorized = $authorization->authorize(
        user: UserEntity::identified(
            id: 'user-1',
            name: 'Thiago Souza',
            login: 'thiago@example.com',
        ),
        now: '2026-07-20 08:31:00',
    );

    expect($authorized->status)->toBe(TemporaryAuthorizationStatusEnum::Expired)
        ->and($authorized->isAuthorized('2026-07-20 08:31:00'))->toBeFalse()
        ->and($authorized->finishedAt?->format('Y-m-d H:i:s'))->toBe('2026-07-20 08:31:00');
});

it('keeps authorization valid across travel report data retries while it has not expired', function (): void {
    $context = validAuthorizationContext();
    $authorization = TemporaryAuthorizationEntity::start(
        context: $context,
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
        timeToLive: new DateInterval('PT10M'),
        issuedAt: '2026-07-20 08:30:00',
        authorizationId: 'authorization-1',
    )->authorize(
        user: UserEntity::identified(
            id: 'user-1',
            name: 'Thiago Souza',
            login: 'thiago@example.com',
        ),
        now: '2026-07-20 08:31:00',
    );

    expect($authorization->allows(ProtectedActionEnum::TravelReportSubmission, $context, '2026-07-20 08:38:00'))
        ->toBeTrue()
        ->and($authorization->allows(
            ProtectedActionEnum::TravelReportSubmission,
            AuthorizationContext::forWhatsappConversation('5571999999999', 'other-conversation'),
            '2026-07-20 08:38:00',
        ))->toBeFalse();
});

it('cancels and revokes temporary authorizations as terminal states', function (): void {
    $authorization = TemporaryAuthorizationEntity::start(
        context: validAuthorizationContext(),
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
        issuedAt: '2026-07-20 08:30:00',
        authorizationId: 'authorization-1',
    );

    $cancelled = $authorization->cancel('2026-07-20 08:32:00');
    $revoked = $authorization->authorize(
        user: UserEntity::identified(
            id: 'user-1',
            name: 'Thiago Souza',
            login: 'thiago@example.com',
        ),
        now: '2026-07-20 08:31:00',
    )->revoke('2026-07-20 08:33:00');

    expect($cancelled->status)->toBe(TemporaryAuthorizationStatusEnum::Cancelled)
        ->and($cancelled->status->isTerminal())->toBeTrue()
        ->and($revoked->status)->toBe(TemporaryAuthorizationStatusEnum::Revoked)
        ->and($revoked->status->isTerminal())->toBeTrue()
        ->and($revoked->isAuthorized('2026-07-20 08:33:30'))->toBeFalse();
});

it('evaluates temporary authorization policy by attempts, action, context and expiration', function (): void {
    $context = validAuthorizationContext();
    $policy = new TemporaryAuthorizationPolicy;
    $authorization = TemporaryAuthorizationEntity::start(
        context: $context,
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
        timeToLive: new DateInterval('PT2M'),
        issuedAt: '2026-07-20 08:30:00',
        authorizationId: 'authorization-1',
    );

    $authorized = $authorization->authorize(
        user: UserEntity::identified(
            id: 'user-1',
            name: 'Thiago Souza',
            login: 'thiago@example.com',
        ),
        now: '2026-07-20 08:31:00',
    );

    expect($policy->canAttempt($authorization, '2026-07-20 08:30:30'))->toBeTrue()
        ->and($policy->canExecuteProtectedAction(
            authorization: $authorized,
            protectedAction: ProtectedActionEnum::TravelReportSubmission,
            context: $context,
            now: '2026-07-20 08:31:30',
        ))->toBeTrue()
        ->and($policy->shouldExpire($authorized, '2026-07-20 08:32:00'))->toBeTrue();
});

it('validates temporary authorization required data with domain exceptions', function (
    Closure $factory,
    string $exception,
    int $code,
    string $message,
): void {
    try {
        $factory();
    } catch (Throwable $throwable) {
        expect($throwable)->toBeInstanceOf($exception)
            ->and($throwable->getCode())->toBe($code)
            ->and($throwable->getMessage())->toBe($message);

        return;
    }

    $this->fail("Expected {$exception} to be thrown.");
})->with([
    'authorization id' => [
        fn () => new TemporaryAuthorizationEntity(
            authorizationId: ' ',
            context: validAuthorizationContext(),
            protectedAction: ProtectedActionEnum::TravelReportSubmission,
        ),
        AuthorizationIdRequiredException::class,
        IdentityCodeExceptionEnum::AuthorizationIdRequired->value,
        'O identificador da autorização temporária é obrigatório.',
    ],
    'whatsapp number' => [
        fn () => AuthorizationContext::forWhatsappConversation(' ', 'conversation-1'),
        WhatsappNumberRequiredException::class,
        IdentityCodeExceptionEnum::WhatsappNumberRequired->value,
        'O número do WhatsApp é obrigatório para a autorização temporária.',
    ],
    'conversation id' => [
        fn () => AuthorizationContext::forWhatsappConversation('5571999999999', ' '),
        ConversationIdRequiredException::class,
        IdentityCodeExceptionEnum::ConversationIdRequired->value,
        'O identificador da conversa é obrigatório para a autorização temporária.',
    ],
    'attempt limit' => [
        fn () => TemporaryAuthorizationEntity::start(
            context: validAuthorizationContext(),
            protectedAction: ProtectedActionEnum::TravelReportSubmission,
            maxAttempts: 0,
            authorizationId: 'authorization-1',
        ),
        InvalidAuthorizationAttemptLimitException::class,
        IdentityCodeExceptionEnum::InvalidAuthorizationAttemptLimit->value,
        'O limite de tentativas da autorização temporária é inválido.',
    ],
    'expiration' => [
        fn () => new TemporaryAuthorizationEntity(
            authorizationId: 'authorization-1',
            context: validAuthorizationContext(),
            protectedAction: ProtectedActionEnum::TravelReportSubmission,
            issuedAt: '2026-07-20 08:30:00',
            expiresAt: '2026-07-20 08:30:00',
        ),
        InvalidAuthorizationExpirationException::class,
        IdentityCodeExceptionEnum::InvalidAuthorizationExpiration->value,
        'A expiração da autorização temporária deve ser posterior à emissão.',
    ],
]);

function validAuthorizationContext(): AuthorizationContext
{
    return AuthorizationContext::forWhatsappConversation(
        whatsappNumber: '5571999999999',
        conversationId: 'conversation-1',
    );
}
