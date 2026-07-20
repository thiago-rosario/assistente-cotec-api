<?php

declare(strict_types=1);

namespace App\Core\Identity\Domain\Validation;

use App\Core\Identity\Exception\AuthorizationIdRequiredException;
use App\Core\Identity\Exception\ConversationIdRequiredException;
use App\Core\Identity\Exception\InvalidAuthorizationAttemptLimitException;
use App\Core\Identity\Exception\InvalidAuthorizationExpirationException;
use App\Core\Identity\Exception\WhatsappNumberRequiredException;
use DateTimeImmutable;

final class TemporaryAuthorizationDomainValidation
{
    public static function validateAuthorizationId(string $authorizationId): void
    {
        if (trim($authorizationId) === '') {
            throw new AuthorizationIdRequiredException;
        }
    }

    public static function validateWhatsappNumber(string $whatsappNumber): void
    {
        if (trim($whatsappNumber) === '') {
            throw new WhatsappNumberRequiredException;
        }
    }

    public static function validateConversationId(string $conversationId): void
    {
        if (trim($conversationId) === '') {
            throw new ConversationIdRequiredException;
        }
    }

    public static function validateAttemptLimit(int $maxAttempts): void
    {
        if ($maxAttempts <= 0) {
            throw new InvalidAuthorizationAttemptLimitException;
        }
    }

    public static function validateFailedAttempts(int $failedAttempts): void
    {
        if ($failedAttempts < 0) {
            throw new InvalidAuthorizationAttemptLimitException;
        }
    }

    public static function validateExpiration(DateTimeImmutable $issuedAt, DateTimeImmutable $expiresAt): void
    {
        if ($expiresAt <= $issuedAt) {
            throw new InvalidAuthorizationExpirationException;
        }
    }
}
