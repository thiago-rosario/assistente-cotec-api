<?php

use App\Core\Identity\Application\DTO\AuthenticateTemporaryAuthorizationInputDTO;
use App\Core\Identity\Application\DTO\CancelTemporaryAuthorizationInputDTO;
use App\Core\Identity\Application\DTO\ConsumeTemporaryAuthorizationInputDTO;
use App\Core\Identity\Application\DTO\StartTemporaryAuthorizationInputDTO;
use App\Core\Identity\Application\DTO\TemporaryAuthorizationOutputDTO;
use App\Core\Identity\Application\DTO\ValidateTemporaryAuthorizationInputDTO;
use App\Core\Identity\Application\Interface\ClockInterface;
use App\Core\Identity\Application\Service\TemporaryAuthorizationCredentialsAuthenticatorService;
use App\Core\Identity\Application\Service\TemporaryAuthorizationErrorCodeService;
use App\Core\Identity\Application\Service\TemporaryAuthorizationFinderService;
use App\Core\Identity\Application\Service\TemporaryAuthorizationGuardService;
use App\Core\Identity\Application\Usecase\AuthenticateTemporaryAuthorizationUsecase;
use App\Core\Identity\Application\Usecase\CancelTemporaryAuthorizationUsecase;
use App\Core\Identity\Application\Usecase\ConsumeTemporaryAuthorizationUsecase;
use App\Core\Identity\Application\Usecase\StartTemporaryAuthorizationUsecase;
use App\Core\Identity\Application\Usecase\ValidateTemporaryAuthorizationUsecase;
use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;
use App\Core\Identity\Domain\Entity\UserEntity;
use App\Core\Identity\Domain\Policy\TemporaryAuthorizationPolicy;
use App\Core\Identity\Domain\Policy\TemporaryAuthorizationStatusPolicy;
use App\Core\Identity\Domain\Repository\TemporaryAuthorizationRepositoryInterface;
use App\Core\Identity\Domain\Repository\UserRepositoryInterface;
use App\Core\Identity\Domain\Service\CredentialsVerifierInterface;
use App\Core\Identity\Domain\ValueObject\AuthorizationContext;
use App\Core\Identity\Domain\ValueObject\Login;
use App\Core\Identity\Enum\IdentityCodeExceptionEnum;
use App\Core\Identity\Enum\ProtectedActionEnum;
use App\Core\Identity\Enum\TemporaryAuthorizationStatusEnum;
use App\Core\Identity\Exception\IdentityApplicationException;
use PHPUnit\Framework\AssertionFailedError;

it('creates a pending temporary authorization', function (): void {
    $repository = new IdentityApplicationTemporaryAuthorizationRepositoryFake;
    $usecase = new StartTemporaryAuthorizationUsecase(
        temporaryAuthorizationRepository: $repository,
        temporaryAuthorizationPolicy: new TemporaryAuthorizationPolicy,
        clock: new IdentityApplicationClockFake('2026-07-20 08:30:00'),
    );

    $output = $usecase(new StartTemporaryAuthorizationInputDTO(
        context: identityApplicationContext(),
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
        maxAttempts: 2,
        timeToLive: new DateInterval('PT5M'),
        authorizationId: 'authorization-1',
    ));

    expect($repository->saved)->toHaveCount(1)
        ->and($output)->toBeInstanceOf(TemporaryAuthorizationOutputDTO::class)
        ->and($output)->not->toBeInstanceOf(TemporaryAuthorizationEntity::class)
        ->and($output->authorizationId)->toBe('authorization-1')
        ->and($output->status)->toBe(TemporaryAuthorizationStatusEnum::PendingCredentials)
        ->and($output->authorizedUserId)->toBeNull()
        ->and($output->failedAttempts)->toBe(0)
        ->and($output->maxAttempts)->toBe(2)
        ->and($output->remainingAttempts)->toBe(2)
        ->and($output->expiresAt->format('Y-m-d H:i:s'))->toBe('2026-07-20 08:35:00');
});

it('does not duplicate an active temporary authorization for the same context and action', function (): void {
    $existingAuthorization = identityApplicationPendingAuthorization(authorizationId: 'authorization-active');
    $repository = new IdentityApplicationTemporaryAuthorizationRepositoryFake($existingAuthorization);

    $output = (new StartTemporaryAuthorizationUsecase(
        temporaryAuthorizationRepository: $repository,
        temporaryAuthorizationPolicy: new TemporaryAuthorizationPolicy,
        clock: new IdentityApplicationClockFake('2026-07-20 08:31:00'),
    ))(new StartTemporaryAuthorizationInputDTO(
        context: identityApplicationContext(),
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
        authorizationId: 'authorization-new',
    ));

    expect($repository->findActiveByContextCount)->toBe(1)
        ->and($repository->saved)->toHaveCount(0)
        ->and($output->authorizationId)->toBe('authorization-active')
        ->and($output->status)->toBe(TemporaryAuthorizationStatusEnum::PendingCredentials);
});

it('expires a stale active temporary authorization before starting a new one', function (): void {
    $staleAuthorization = identityApplicationPendingAuthorization(
        authorizationId: 'authorization-stale',
        timeToLive: new DateInterval('PT1M'),
    );
    $repository = new IdentityApplicationTemporaryAuthorizationRepositoryFake($staleAuthorization);

    $output = (new StartTemporaryAuthorizationUsecase(
        temporaryAuthorizationRepository: $repository,
        temporaryAuthorizationPolicy: new TemporaryAuthorizationPolicy,
        clock: new IdentityApplicationClockFake('2026-07-20 08:31:00'),
    ))(new StartTemporaryAuthorizationInputDTO(
        context: identityApplicationContext(),
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
        authorizationId: 'authorization-new',
    ));

    expect($repository->saved)->toHaveCount(2)
        ->and($repository->saved[0]->authorizationId)->toBe('authorization-stale')
        ->and($repository->saved[0]->status)->toBe(TemporaryAuthorizationStatusEnum::Expired)
        ->and($repository->saved[1]->authorizationId)->toBe('authorization-new')
        ->and($repository->saved[1]->status)->toBe(TemporaryAuthorizationStatusEnum::PendingCredentials)
        ->and($output->authorizationId)->toBe('authorization-new');
});

it('authenticates temporary authorization with valid credentials', function (): void {
    $authorization = identityApplicationPendingAuthorization();
    $user = identityApplicationUser();
    $repository = new IdentityApplicationTemporaryAuthorizationRepositoryFake($authorization);
    $userRepository = new IdentityApplicationUserRepositoryFake($user);
    $credentialsVerifier = new IdentityApplicationCredentialsVerifierFake($user);
    $input = new AuthenticateTemporaryAuthorizationInputDTO(
        authorizationId: 'authorization-1',
        context: identityApplicationContext(),
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
        login: Login::fromString('thiago@example.com'),
        plainPassword: 'secret-release',
    );

    $output = identityApplicationAuthenticateUsecase(
        repository: $repository,
        userRepository: $userRepository,
        credentialsVerifier: $credentialsVerifier,
        clock: new IdentityApplicationClockFake('2026-07-20 08:31:00'),
    )($input);

    expect($userRepository->findByLoginCount)->toBe(1)
        ->and($credentialsVerifier->verifyCount)->toBe(1)
        ->and($repository->saved)->toHaveCount(1)
        ->and($repository->saved[0]->status)->toBe(TemporaryAuthorizationStatusEnum::Authorized)
        ->and($repository->saved[0]->authorizedUserId)->toBe('user-1')
        ->and($output)->toBeInstanceOf(TemporaryAuthorizationOutputDTO::class)
        ->and($output)->not->toBeInstanceOf(TemporaryAuthorizationEntity::class)
        ->and($output->status)->toBe(TemporaryAuthorizationStatusEnum::Authorized)
        ->and($output->authorizedUserId)->toBe('user-1')
        ->and(str_contains(serialize($output), 'secret-release'))->toBeFalse();
});

it('treats an unknown login as invalid credentials', function (): void {
    $repository = new IdentityApplicationTemporaryAuthorizationRepositoryFake(identityApplicationPendingAuthorization());
    $credentialsVerifier = new IdentityApplicationCredentialsVerifierFake;

    $exception = identityApplicationExceptionFor(fn () => identityApplicationAuthenticateUsecase(
        repository: $repository,
        userRepository: new IdentityApplicationUserRepositoryFake,
        credentialsVerifier: $credentialsVerifier,
        clock: new IdentityApplicationClockFake('2026-07-20 08:31:00'),
    )(identityApplicationAuthenticateInput(login: 'missing@example.com')));

    expect($exception->identityCode)->toBe(IdentityCodeExceptionEnum::InvalidTemporaryAuthorizationCredentials)
        ->and($repository->saved)->toHaveCount(1)
        ->and($repository->saved[0]->failedAttempts)->toBe(1)
        ->and($repository->saved[0]->status)->toBe(TemporaryAuthorizationStatusEnum::PendingCredentials)
        ->and($credentialsVerifier->verifyCount)->toBe(1);
});

it('treats an invalid password as invalid credentials and records the failed attempt', function (): void {
    $user = identityApplicationUser();
    $repository = new IdentityApplicationTemporaryAuthorizationRepositoryFake(identityApplicationPendingAuthorization());

    $exception = identityApplicationExceptionFor(fn () => identityApplicationAuthenticateUsecase(
        repository: $repository,
        userRepository: new IdentityApplicationUserRepositoryFake($user),
        credentialsVerifier: new IdentityApplicationCredentialsVerifierFake,
        clock: new IdentityApplicationClockFake('2026-07-20 08:31:00'),
    )(identityApplicationAuthenticateInput()));

    expect($exception->identityCode)->toBe(IdentityCodeExceptionEnum::InvalidTemporaryAuthorizationCredentials)
        ->and($repository->saved)->toHaveCount(1)
        ->and($repository->saved[0]->failedAttempts)->toBe(1)
        ->and($repository->saved[0]->remainingAttempts())->toBe(2)
        ->and($repository->saved[0]->status)->toBe(TemporaryAuthorizationStatusEnum::PendingCredentials);
});

it('moves temporary authorization to attempts exceeded when the limit is reached', function (): void {
    $repository = new IdentityApplicationTemporaryAuthorizationRepositoryFake(identityApplicationPendingAuthorization(maxAttempts: 1));

    $exception = identityApplicationExceptionFor(fn () => identityApplicationAuthenticateUsecase(
        repository: $repository,
        userRepository: new IdentityApplicationUserRepositoryFake(identityApplicationUser()),
        credentialsVerifier: new IdentityApplicationCredentialsVerifierFake,
        clock: new IdentityApplicationClockFake('2026-07-20 08:31:00'),
    )(identityApplicationAuthenticateInput()));

    expect($exception->identityCode)->toBe(IdentityCodeExceptionEnum::TemporaryAuthorizationAttemptsExceeded)
        ->and($repository->saved)->toHaveCount(1)
        ->and($repository->saved[0]->failedAttempts)->toBe(1)
        ->and($repository->saved[0]->status)->toBe(TemporaryAuthorizationStatusEnum::AttemptsExceeded)
        ->and($repository->saved[0]->finishedAt?->format('Y-m-d H:i:s'))->toBe('2026-07-20 08:31:00');
});

it('blocks a new authentication attempt after the attempt limit was exceeded', function (): void {
    $attemptsExceededAuthorization = identityApplicationPendingAuthorization(maxAttempts: 1)
        ->recordFailedAttempt('2026-07-20 08:31:00');
    $repository = new IdentityApplicationTemporaryAuthorizationRepositoryFake($attemptsExceededAuthorization);
    $userRepository = new IdentityApplicationUserRepositoryFake(identityApplicationUser());
    $credentialsVerifier = new IdentityApplicationCredentialsVerifierFake(identityApplicationUser());

    $exception = identityApplicationExceptionFor(fn () => identityApplicationAuthenticateUsecase(
        repository: $repository,
        userRepository: $userRepository,
        credentialsVerifier: $credentialsVerifier,
        clock: new IdentityApplicationClockFake('2026-07-20 08:32:00'),
    )(identityApplicationAuthenticateInput()));

    expect($exception->identityCode)->toBe(IdentityCodeExceptionEnum::TemporaryAuthorizationAttemptsExceeded)
        ->and($repository->saved)->toHaveCount(0)
        ->and($userRepository->findByLoginCount)->toBe(0)
        ->and($credentialsVerifier->verifyCount)->toBe(0);
});

it('blocks authentication in an expired temporary authorization and persists the expired status', function (): void {
    $expiredByTimeAuthorization = identityApplicationPendingAuthorization(
        timeToLive: new DateInterval('PT1M'),
    );
    $repository = new IdentityApplicationTemporaryAuthorizationRepositoryFake($expiredByTimeAuthorization);
    $credentialsVerifier = new IdentityApplicationCredentialsVerifierFake(identityApplicationUser());

    $exception = identityApplicationExceptionFor(fn () => identityApplicationAuthenticateUsecase(
        repository: $repository,
        userRepository: new IdentityApplicationUserRepositoryFake(identityApplicationUser()),
        credentialsVerifier: $credentialsVerifier,
        clock: new IdentityApplicationClockFake('2026-07-20 08:31:00'),
    )(identityApplicationAuthenticateInput()));

    expect($exception->identityCode)->toBe(IdentityCodeExceptionEnum::TemporaryAuthorizationExpired)
        ->and($repository->saved)->toHaveCount(1)
        ->and($repository->saved[0]->status)->toBe(TemporaryAuthorizationStatusEnum::Expired)
        ->and($repository->saved[0]->finishedAt?->format('Y-m-d H:i:s'))->toBe('2026-07-20 08:31:00')
        ->and($credentialsVerifier->verifyCount)->toBe(0);
});

it('validates an authorized temporary authorization for the matching context and action', function (): void {
    $authorization = identityApplicationAuthorizedAuthorization();
    $repository = new IdentityApplicationTemporaryAuthorizationRepositoryFake($authorization);

    $output = identityApplicationValidateUsecase(
        repository: $repository,
        clock: new IdentityApplicationClockFake('2026-07-20 08:32:00'),
    )(new ValidateTemporaryAuthorizationInputDTO(
        authorizationId: 'authorization-1',
        context: identityApplicationContext(),
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
    ));

    expect($repository->saved)->toHaveCount(0)
        ->and($output->authorizationId)->toBe('authorization-1')
        ->and($output->authorizedUserId)->toBe('user-1')
        ->and($output->status)->toBe(TemporaryAuthorizationStatusEnum::Authorized)
        ->and($output->authorizedAt?->format('Y-m-d H:i:s'))->toBe('2026-07-20 08:31:00');
});

it('rejects validation while credentials are still pending', function (): void {
    $repository = new IdentityApplicationTemporaryAuthorizationRepositoryFake(identityApplicationPendingAuthorization());

    $exception = identityApplicationExceptionFor(fn () => identityApplicationValidateUsecase(
        repository: $repository,
        clock: new IdentityApplicationClockFake('2026-07-20 08:31:00'),
    )(identityApplicationValidateInput()));

    expect($exception->identityCode)->toBe(IdentityCodeExceptionEnum::TemporaryAuthorizationPendingCredentials)
        ->and($repository->saved)->toHaveCount(0);
});

it('rejects validation for a different context without persisting changes', function (): void {
    $repository = new IdentityApplicationTemporaryAuthorizationRepositoryFake(identityApplicationAuthorizedAuthorization());

    $exception = identityApplicationExceptionFor(fn () => identityApplicationValidateUsecase(
        repository: $repository,
        clock: new IdentityApplicationClockFake('2026-07-20 08:32:00'),
    )(new ValidateTemporaryAuthorizationInputDTO(
        authorizationId: 'authorization-1',
        context: identityApplicationOtherContext(),
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
    )));

    expect($exception->identityCode)->toBe(IdentityCodeExceptionEnum::TemporaryAuthorizationContextMismatch)
        ->and($repository->saved)->toHaveCount(0);
});

it('rejects validation for a different protected action without persisting changes', function (): void {
    $repository = new IdentityApplicationTemporaryAuthorizationRepositoryFake(identityApplicationAuthorizedAuthorization());

    $exception = identityApplicationExceptionFor(fn () => identityApplicationValidateUsecase(
        repository: $repository,
        clock: new IdentityApplicationClockFake('2026-07-20 08:32:00'),
    )(new ValidateTemporaryAuthorizationInputDTO(
        authorizationId: 'authorization-1',
        context: identityApplicationContext(),
        protectedAction: ProtectedActionEnum::BuildPanelConsultation,
    )));

    expect($exception->identityCode)->toBe(IdentityCodeExceptionEnum::TemporaryAuthorizationProtectedActionMismatch)
        ->and($repository->saved)->toHaveCount(0);
});

it('persists the expired status when validation finds an expired authorization', function (): void {
    $repository = new IdentityApplicationTemporaryAuthorizationRepositoryFake(identityApplicationAuthorizedAuthorization(
        timeToLive: new DateInterval('PT2M'),
    ));

    $exception = identityApplicationExceptionFor(fn () => identityApplicationValidateUsecase(
        repository: $repository,
        clock: new IdentityApplicationClockFake('2026-07-20 08:32:00'),
    )(identityApplicationValidateInput()));

    expect($exception->identityCode)->toBe(IdentityCodeExceptionEnum::TemporaryAuthorizationExpired)
        ->and($repository->saved)->toHaveCount(1)
        ->and($repository->saved[0]->status)->toBe(TemporaryAuthorizationStatusEnum::Expired);
});

it('cancels a pending temporary authorization', function (): void {
    $repository = new IdentityApplicationTemporaryAuthorizationRepositoryFake(identityApplicationPendingAuthorization());

    $output = identityApplicationCancelUsecase(
        repository: $repository,
        clock: new IdentityApplicationClockFake('2026-07-20 08:32:00'),
    )(new CancelTemporaryAuthorizationInputDTO(
        authorizationId: 'authorization-1',
        context: identityApplicationContext(),
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
    ));

    expect($repository->saved)->toHaveCount(1)
        ->and($repository->saved[0]->status)->toBe(TemporaryAuthorizationStatusEnum::Cancelled)
        ->and($output->status)->toBe(TemporaryAuthorizationStatusEnum::Cancelled)
        ->and($output->finishedAt?->format('Y-m-d H:i:s'))->toBe('2026-07-20 08:32:00');
});

it('cancels an authorized temporary authorization when the domain allows it', function (): void {
    $repository = new IdentityApplicationTemporaryAuthorizationRepositoryFake(identityApplicationAuthorizedAuthorization());

    $output = identityApplicationCancelUsecase(
        repository: $repository,
        clock: new IdentityApplicationClockFake('2026-07-20 08:32:00'),
    )(new CancelTemporaryAuthorizationInputDTO(
        authorizationId: 'authorization-1',
        context: identityApplicationContext(),
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
    ));

    expect($repository->saved)->toHaveCount(1)
        ->and($repository->saved[0]->status)->toBe(TemporaryAuthorizationStatusEnum::Cancelled)
        ->and($output->status)->toBe(TemporaryAuthorizationStatusEnum::Cancelled);
});

it('keeps cancellation idempotent when authorization is already cancelled', function (): void {
    $cancelledAuthorization = identityApplicationPendingAuthorization()->cancel('2026-07-20 08:31:00');
    $repository = new IdentityApplicationTemporaryAuthorizationRepositoryFake($cancelledAuthorization);

    $output = identityApplicationCancelUsecase(
        repository: $repository,
        clock: new IdentityApplicationClockFake('2026-07-20 08:32:00'),
    )(new CancelTemporaryAuthorizationInputDTO(
        authorizationId: 'authorization-1',
        context: identityApplicationContext(),
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
    ));

    expect($repository->saved)->toHaveCount(0)
        ->and($output->status)->toBe(TemporaryAuthorizationStatusEnum::Cancelled);
});

it('revokes an authorized temporary authorization after the protected action is consumed', function (): void {
    $repository = new IdentityApplicationTemporaryAuthorizationRepositoryFake(identityApplicationAuthorizedAuthorization());

    $output = identityApplicationConsumeUsecase(
        repository: $repository,
        clock: new IdentityApplicationClockFake('2026-07-20 08:32:00'),
    )(new ConsumeTemporaryAuthorizationInputDTO(
        authorizationId: 'authorization-1',
        context: identityApplicationContext(),
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
    ));

    expect($repository->saved)->toHaveCount(1)
        ->and($repository->saved[0]->status)->toBe(TemporaryAuthorizationStatusEnum::Revoked)
        ->and($output->status)->toBe(TemporaryAuthorizationStatusEnum::Revoked)
        ->and($output->finishedAt?->format('Y-m-d H:i:s'))->toBe('2026-07-20 08:32:00');
});

it('prevents a consumed temporary authorization from being reused', function (): void {
    $revokedAuthorization = identityApplicationAuthorizedAuthorization()->revoke('2026-07-20 08:32:00');
    $repository = new IdentityApplicationTemporaryAuthorizationRepositoryFake($revokedAuthorization);

    $exception = identityApplicationExceptionFor(fn () => identityApplicationValidateUsecase(
        repository: $repository,
        clock: new IdentityApplicationClockFake('2026-07-20 08:33:00'),
    )(identityApplicationValidateInput()));

    expect($exception->identityCode)->toBe(IdentityCodeExceptionEnum::TemporaryAuthorizationRevoked)
        ->and($repository->saved)->toHaveCount(0);
});

it('does not allow a terminal authorization to become authorized again', function (): void {
    $revokedAuthorization = identityApplicationAuthorizedAuthorization()->revoke('2026-07-20 08:32:00');
    $repository = new IdentityApplicationTemporaryAuthorizationRepositoryFake($revokedAuthorization);
    $credentialsVerifier = new IdentityApplicationCredentialsVerifierFake(identityApplicationUser());

    $exception = identityApplicationExceptionFor(fn () => identityApplicationAuthenticateUsecase(
        repository: $repository,
        userRepository: new IdentityApplicationUserRepositoryFake(identityApplicationUser()),
        credentialsVerifier: $credentialsVerifier,
        clock: new IdentityApplicationClockFake('2026-07-20 08:33:00'),
    )(identityApplicationAuthenticateInput()));

    expect($exception->identityCode)->toBe(IdentityCodeExceptionEnum::TemporaryAuthorizationRevoked)
        ->and($repository->saved)->toHaveCount(0)
        ->and($credentialsVerifier->verifyCount)->toBe(0);
});

it('returns not found when the temporary authorization does not exist', function (): void {
    $exception = identityApplicationExceptionFor(fn () => identityApplicationValidateUsecase(
        repository: new IdentityApplicationTemporaryAuthorizationRepositoryFake,
        clock: new IdentityApplicationClockFake('2026-07-20 08:32:00'),
    )(identityApplicationValidateInput()));

    expect($exception->identityCode)->toBe(IdentityCodeExceptionEnum::TemporaryAuthorizationNotFound)
        ->and($exception->getCode())->toBe(IdentityCodeExceptionEnum::TemporaryAuthorizationNotFound->value);
});

function identityApplicationContext(): AuthorizationContext
{
    return AuthorizationContext::forWhatsappConversation(
        whatsappNumber: '5571999999999',
        conversationId: 'conversation-1',
    );
}

function identityApplicationOtherContext(): AuthorizationContext
{
    return AuthorizationContext::forWhatsappConversation(
        whatsappNumber: '5571888888888',
        conversationId: 'conversation-2',
    );
}

function identityApplicationPendingAuthorization(
    string $authorizationId = 'authorization-1',
    int $maxAttempts = 3,
    ?DateInterval $timeToLive = null,
): TemporaryAuthorizationEntity {
    return TemporaryAuthorizationEntity::start(
        context: identityApplicationContext(),
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
        maxAttempts: $maxAttempts,
        timeToLive: $timeToLive,
        issuedAt: '2026-07-20 08:30:00',
        authorizationId: $authorizationId,
    );
}

function identityApplicationAuthorizedAuthorization(
    ?DateInterval $timeToLive = null,
): TemporaryAuthorizationEntity {
    return identityApplicationPendingAuthorization(timeToLive: $timeToLive)->authorize(
        user: identityApplicationUser(),
        now: '2026-07-20 08:31:00',
    );
}

function identityApplicationUser(string $id = 'user-1', string $login = 'thiago@example.com'): UserEntity
{
    return UserEntity::identified(
        id: $id,
        name: 'Thiago Souza',
        login: $login,
    );
}

function identityApplicationAuthenticateInput(string $login = 'thiago@example.com'): AuthenticateTemporaryAuthorizationInputDTO
{
    return new AuthenticateTemporaryAuthorizationInputDTO(
        authorizationId: 'authorization-1',
        context: identityApplicationContext(),
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
        login: Login::fromString($login),
        plainPassword: 'secret-release',
    );
}

function identityApplicationAuthenticateUsecase(
    IdentityApplicationTemporaryAuthorizationRepositoryFake $repository,
    IdentityApplicationUserRepositoryFake $userRepository,
    CredentialsVerifierInterface $credentialsVerifier,
    ClockInterface $clock,
): AuthenticateTemporaryAuthorizationUsecase {
    $policy = new TemporaryAuthorizationPolicy;

    return new AuthenticateTemporaryAuthorizationUsecase(
        finder: new TemporaryAuthorizationFinderService($repository),
        guard: new TemporaryAuthorizationGuardService($policy, $repository, new TemporaryAuthorizationErrorCodeService),
        credentialsAuthenticator: new TemporaryAuthorizationCredentialsAuthenticatorService($userRepository, $credentialsVerifier, $repository),
        temporaryAuthorizationRepository: $repository,
        clock: $clock,
    );
}

function identityApplicationCancelUsecase(
    IdentityApplicationTemporaryAuthorizationRepositoryFake $repository,
    ClockInterface $clock,
): CancelTemporaryAuthorizationUsecase {
    $policy = new TemporaryAuthorizationPolicy;

    return new CancelTemporaryAuthorizationUsecase(
        finder: new TemporaryAuthorizationFinderService($repository),
        guard: new TemporaryAuthorizationGuardService($policy, $repository, new TemporaryAuthorizationErrorCodeService),
        temporaryAuthorizationRepository: $repository,
        clock: $clock,
    );
}

function identityApplicationConsumeUsecase(
    IdentityApplicationTemporaryAuthorizationRepositoryFake $repository,
    ClockInterface $clock,
): ConsumeTemporaryAuthorizationUsecase {
    $policy = new TemporaryAuthorizationPolicy;

    return new ConsumeTemporaryAuthorizationUsecase(
        finder: new TemporaryAuthorizationFinderService($repository),
        guard: new TemporaryAuthorizationGuardService($policy, $repository, new TemporaryAuthorizationErrorCodeService),
        temporaryAuthorizationRepository: $repository,
        clock: $clock,
    );
}

function identityApplicationValidateUsecase(
    IdentityApplicationTemporaryAuthorizationRepositoryFake $repository,
    ClockInterface $clock,
): ValidateTemporaryAuthorizationUsecase {
    $policy = new TemporaryAuthorizationPolicy;

    return new ValidateTemporaryAuthorizationUsecase(
        finder: new TemporaryAuthorizationFinderService($repository),
        guard: new TemporaryAuthorizationGuardService($policy, $repository, new TemporaryAuthorizationErrorCodeService),
        clock: $clock,
    );
}

function identityApplicationValidateInput(): ValidateTemporaryAuthorizationInputDTO
{
    return new ValidateTemporaryAuthorizationInputDTO(
        authorizationId: 'authorization-1',
        context: identityApplicationContext(),
        protectedAction: ProtectedActionEnum::TravelReportSubmission,
    );
}

function identityApplicationExceptionFor(Closure $operation): IdentityApplicationException
{
    try {
        $operation();
    } catch (IdentityApplicationException $exception) {
        expect($exception->getCode())->toBe($exception->identityCode->value);

        return $exception;
    }

    throw new AssertionFailedError('Expected an identity application exception to be thrown.');
}

final class IdentityApplicationClockFake implements ClockInterface
{
    private readonly DateTimeImmutable $currentTime;

    public function __construct(DateTimeImmutable|string $currentTime)
    {
        $this->currentTime = $currentTime instanceof DateTimeImmutable
            ? $currentTime
            : new DateTimeImmutable($currentTime);
    }

    public function now(): DateTimeImmutable
    {
        return $this->currentTime;
    }
}

final class IdentityApplicationTemporaryAuthorizationRepositoryFake implements TemporaryAuthorizationRepositoryInterface
{
    /**
     * @var array<string, TemporaryAuthorizationEntity>
     */
    public array $authorizations = [];

    /**
     * @var list<TemporaryAuthorizationEntity>
     */
    public array $saved = [];

    public int $findActiveByContextCount = 0;

    public function __construct(TemporaryAuthorizationEntity ...$authorizations)
    {
        foreach ($authorizations as $authorization) {
            $this->authorizations[$authorization->authorizationId] = $authorization;
        }
    }

    public function save(TemporaryAuthorizationEntity $authorization): TemporaryAuthorizationEntity
    {
        $this->saved[] = $authorization;
        $this->authorizations[$authorization->authorizationId] = $authorization;

        return $authorization;
    }

    public function findById(string $authorizationId): ?TemporaryAuthorizationEntity
    {
        return $this->authorizations[$authorizationId] ?? null;
    }

    public function findActiveByContext(
        AuthorizationContext $context,
        ProtectedActionEnum $protectedAction,
        DateTimeInterface|string|null $now = null,
    ): ?TemporaryAuthorizationEntity {
        $this->findActiveByContextCount++;

        foreach ($this->authorizations as $authorization) {
            if (! $authorization->context->equals($context)) {
                continue;
            }

            if ($authorization->protectedAction !== $protectedAction) {
                continue;
            }

            if (TemporaryAuthorizationStatusPolicy::isTerminal($authorization->status)) {
                continue;
            }

            return $authorization;
        }

        return null;
    }
}

final class IdentityApplicationUserRepositoryFake implements UserRepositoryInterface
{
    /**
     * @var array<string, UserEntity>
     */
    private array $usersByLogin = [];

    public int $findByLoginCount = 0;

    public function __construct(UserEntity ...$users)
    {
        foreach ($users as $user) {
            $this->usersByLogin[(string) $user->login] = $user;
        }
    }

    public function insert(UserEntity $user, string $plainPassword): UserEntity
    {
        $this->usersByLogin[(string) $user->login] = $user;

        return $user;
    }

    public function all(): array
    {
        return array_values($this->usersByLogin);
    }

    public function findById(string $id): ?UserEntity
    {
        foreach ($this->usersByLogin as $user) {
            if ($user->isSameUser($id)) {
                return $user;
            }
        }

        return null;
    }

    public function findByLogin(Login $login): ?UserEntity
    {
        $this->findByLoginCount++;

        return $this->usersByLogin[(string) $login] ?? null;
    }

    public function update(UserEntity $user): UserEntity
    {
        $this->usersByLogin[(string) $user->login] = $user;

        return $user;
    }

    public function updatePassword(string $id, string $plainPassword): bool
    {
        return $this->findById($id) !== null;
    }

    public function delete(string $id): bool
    {
        foreach ($this->usersByLogin as $login => $user) {
            if ($user->isSameUser($id)) {
                unset($this->usersByLogin[$login]);

                return true;
            }
        }

        return false;
    }
}

final class IdentityApplicationCredentialsVerifierFake implements CredentialsVerifierInterface
{
    public int $verifyCount = 0;

    public function __construct(
        private readonly ?UserEntity $verifiedUser = null,
    ) {}

    public function verify(Login $login, string $plainPassword): ?UserEntity
    {
        $this->verifyCount++;

        return $this->verifiedUser;
    }
}
