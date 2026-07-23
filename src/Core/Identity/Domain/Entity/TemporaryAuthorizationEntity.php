<?php

declare(strict_types=1);

namespace App\Core\Identity\Domain\Entity;

use App\Core\Identity\Domain\Policy\TemporaryAuthorizationStatusPolicy;
use App\Core\Identity\Domain\Trait\DateTimeConversionTrait;
use App\Core\Identity\Domain\Trait\MethodsMagicsTrait;
use App\Core\Identity\Domain\Validation\TemporaryAuthorizationDomainValidation;
use App\Core\Identity\Domain\Validation\UserDomainValidation;
use App\Core\Identity\Domain\ValueObject\AuthorizationContext;
use App\Core\Identity\Enum\ProtectedActionEnum;
use App\Core\Identity\Enum\TemporaryAuthorizationStatusEnum;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * @property-read string $authorizationId
 * @property-read AuthorizationContext $context
 * @property-read ProtectedActionEnum $protectedAction
 * @property-read TemporaryAuthorizationStatusEnum $status
 * @property-read string|null $authorizedUserId
 * @property-read int $failedAttempts
 * @property-read int $maxAttempts
 * @property-read DateTimeImmutable $issuedAt
 * @property-read DateTimeImmutable $expiresAt
 * @property-read DateTimeImmutable|null $authorizedAt
 * @property-read DateTimeImmutable|null $finishedAt
 */
final class TemporaryAuthorizationEntity
{
    use DateTimeConversionTrait;
    use MethodsMagicsTrait;

    protected readonly string $authorizationId;

    protected readonly AuthorizationContext $context;

    protected readonly ProtectedActionEnum $protectedAction;

    protected readonly TemporaryAuthorizationStatusEnum $status;

    protected readonly ?string $authorizedUserId;

    protected readonly int $failedAttempts;

    protected readonly int $maxAttempts;

    protected readonly DateTimeImmutable $issuedAt;

    protected readonly DateTimeImmutable $expiresAt;

    protected readonly ?DateTimeImmutable $authorizedAt;

    protected readonly ?DateTimeImmutable $finishedAt;

    public function __construct(
        string $authorizationId,
        AuthorizationContext $context,
        ProtectedActionEnum $protectedAction,
        TemporaryAuthorizationStatusEnum $status = TemporaryAuthorizationStatusEnum::PendingCredentials,
        ?string $authorizedUserId = null,
        int $failedAttempts = 0,
        int $maxAttempts = 3,
        DateTimeInterface|string|null $issuedAt = null,
        DateTimeInterface|string|null $expiresAt = null,
        DateTimeInterface|string|null $authorizedAt = null,
        DateTimeInterface|string|null $finishedAt = null,
    ) {
        $this->authorizationId = trim($authorizationId);
        $this->context = $context;
        $this->protectedAction = $protectedAction;
        $this->status = $status;
        $this->authorizedUserId = $authorizedUserId === null ? null : trim($authorizedUserId);
        $this->failedAttempts = $failedAttempts;
        $this->maxAttempts = $maxAttempts;
        $this->issuedAt = self::dateTime($issuedAt);
        $this->expiresAt = $expiresAt === null
            ? $this->issuedAt->add(new DateInterval('PT10M'))
            : self::dateTime($expiresAt);
        $this->authorizedAt = self::nullableDateTime($authorizedAt);
        $this->finishedAt = self::nullableDateTime($finishedAt);

        $this->validate();
    }

    public static function start(
        AuthorizationContext $context,
        ProtectedActionEnum $protectedAction,
        int $maxAttempts = 3,
        ?DateInterval $timeToLive = null,
        DateTimeInterface|string|null $issuedAt = null,
        ?string $authorizationId = null,
    ): self {
        $issuedAt = self::dateTime($issuedAt);

        return new self(
            authorizationId: $authorizationId ?? self::newAuthorizationId(),
            context: $context,
            protectedAction: $protectedAction,
            maxAttempts: $maxAttempts,
            issuedAt: $issuedAt,
            expiresAt: $issuedAt->add($timeToLive ?? new DateInterval('PT10M')),
        );
    }

    public function recordFailedAttempt(DateTimeInterface|string|null $now = null): self
    {
        $now = self::dateTime($now);

        if ($this->hasExpired($now)) {
            return $this->expire($now);
        }

        if (! $this->canReceiveCredentialAttempt($now)) {
            return $this;
        }

        $failedAttempts = $this->failedAttempts + 1;
        $status = $failedAttempts >= $this->maxAttempts
            ? TemporaryAuthorizationStatusEnum::AttemptsExceeded
            : TemporaryAuthorizationStatusEnum::PendingCredentials;

        return $this->rebuild(
            status: $status,
            failedAttempts: $failedAttempts,
            finishedAt: TemporaryAuthorizationStatusPolicy::isTerminal($status) ? $now : $this->finishedAt,
        );
    }

    public function authorize(UserEntity $user, DateTimeInterface|string|null $now = null): self
    {
        $now = self::dateTime($now);

        if ($this->hasExpired($now)) {
            return $this->expire($now);
        }

        if (! $this->canReceiveCredentialAttempt($now)) {
            return $this;
        }

        return $this->rebuild(
            status: TemporaryAuthorizationStatusEnum::Authorized,
            authorizedUserId: $user->id,
            authorizedAt: $now,
            finishedAt: null,
        );
    }

    public function expire(DateTimeInterface|string|null $now = null): self
    {
        return $this->rebuild(
            status: TemporaryAuthorizationStatusEnum::Expired,
            finishedAt: self::dateTime($now),
        );
    }

    public function cancel(DateTimeInterface|string|null $now = null): self
    {
        return $this->rebuild(
            status: TemporaryAuthorizationStatusEnum::Cancelled,
            finishedAt: self::dateTime($now),
        );
    }

    public function revoke(DateTimeInterface|string|null $now = null): self
    {
        return $this->rebuild(
            status: TemporaryAuthorizationStatusEnum::Revoked,
            finishedAt: self::dateTime($now),
        );
    }

    public function canReceiveCredentialAttempt(DateTimeInterface|string|null $now = null): bool
    {
        return TemporaryAuthorizationStatusPolicy::isPending($this->status)
            && ! $this->hasExpired($now)
            && $this->failedAttempts < $this->maxAttempts;
    }

    public function isAuthorized(DateTimeInterface|string|null $now = null): bool
    {
        return TemporaryAuthorizationStatusPolicy::isAuthorized($this->status)
            && $this->authorizedUserId !== null
            && ! $this->hasExpired($now);
    }

    public function hasExpired(DateTimeInterface|string|null $now = null): bool
    {
        return $this->status === TemporaryAuthorizationStatusEnum::Expired
            || self::dateTime($now) >= $this->expiresAt;
    }

    public function hasAttemptsExceeded(): bool
    {
        return $this->status === TemporaryAuthorizationStatusEnum::AttemptsExceeded
            || $this->failedAttempts >= $this->maxAttempts;
    }

    public function remainingAttempts(): int
    {
        return max(0, $this->maxAttempts - $this->failedAttempts);
    }

    public function allows(
        ProtectedActionEnum $protectedAction,
        AuthorizationContext $context,
        DateTimeInterface|string|null $now = null,
    ): bool {
        return $this->protectedAction === $protectedAction
            && $this->context->equals($context)
            && $this->isAuthorized($now);
    }

    /**
     * @return array{
     *     authorization_id: string,
     *     whatsapp_number: string,
     *     conversation_id: string,
     *     protected_action: string,
     *     status: string,
     *     authorized_user_id: string|null,
     *     failed_attempts: int,
     *     max_attempts: int,
     *     issued_at: string,
     *     expires_at: string,
     *     authorized_at: string|null,
     *     finished_at: string|null
     * }
     */
    public function toStateArray(): array
    {
        return [
            'authorization_id' => $this->authorizationId,
            'whatsapp_number' => $this->context->whatsappNumber,
            'conversation_id' => $this->context->conversationId,
            'protected_action' => $this->protectedAction->value,
            'status' => $this->status->value,
            'authorized_user_id' => $this->authorizedUserId,
            'failed_attempts' => $this->failedAttempts,
            'max_attempts' => $this->maxAttempts,
            'issued_at' => $this->issuedAt->format(DateTimeInterface::ATOM),
            'expires_at' => $this->expiresAt->format(DateTimeInterface::ATOM),
            'authorized_at' => $this->authorizedAt?->format(DateTimeInterface::ATOM),
            'finished_at' => $this->finishedAt?->format(DateTimeInterface::ATOM),
        ];
    }

    private static function newAuthorizationId(): string
    {
        return bin2hex(random_bytes(16));
    }

    private function validate(): void
    {
        TemporaryAuthorizationDomainValidation::validateAuthorizationId($this->authorizationId);
        TemporaryAuthorizationDomainValidation::validateAttemptLimit($this->maxAttempts);
        TemporaryAuthorizationDomainValidation::validateFailedAttempts($this->failedAttempts);
        TemporaryAuthorizationDomainValidation::validateExpiration($this->issuedAt, $this->expiresAt);

        if (TemporaryAuthorizationStatusPolicy::isAuthorized($this->status)) {
            UserDomainValidation::validateId((string) $this->authorizedUserId);
        }
    }

    private function rebuild(
        ?TemporaryAuthorizationStatusEnum $status = null,
        ?string $authorizedUserId = null,
        ?int $failedAttempts = null,
        DateTimeInterface|string|null $authorizedAt = null,
        DateTimeInterface|string|null $finishedAt = null,
    ): self {
        return new self(
            authorizationId: $this->authorizationId,
            context: $this->context,
            protectedAction: $this->protectedAction,
            status: $status ?? $this->status,
            authorizedUserId: $authorizedUserId ?? $this->authorizedUserId,
            failedAttempts: $failedAttempts ?? $this->failedAttempts,
            maxAttempts: $this->maxAttempts,
            issuedAt: $this->issuedAt,
            expiresAt: $this->expiresAt,
            authorizedAt: $authorizedAt ?? $this->authorizedAt,
            finishedAt: $finishedAt,
        );
    }
}
