<?php

declare(strict_types=1);

namespace App\Core\Identity\Infra\Repository\Cache;

use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;
use App\Core\Identity\Domain\ValueObject\AuthorizationContext;
use App\Core\Identity\Enum\ProtectedActionEnum;
use App\Core\Identity\Enum\TemporaryAuthorizationStatusEnum;
use Throwable;

final class TemporaryAuthorizationStateMapper
{
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
    public function toState(TemporaryAuthorizationEntity $authorization): array
    {
        return $authorization->toStateArray();
    }

    public function fromState(mixed $state): ?TemporaryAuthorizationEntity
    {
        if (! is_array($state)) {
            return null;
        }

        $authorizationId = $this->stringValue($state['authorization_id'] ?? null);
        $whatsappNumber = $this->stringValue($state['whatsapp_number'] ?? null);
        $conversationId = $this->stringValue($state['conversation_id'] ?? null);
        $protectedAction = $this->protectedAction($state['protected_action'] ?? null);
        $status = $this->status($state['status'] ?? null);
        $failedAttempts = $this->integerValue($state['failed_attempts'] ?? null);
        $maxAttempts = $this->integerValue($state['max_attempts'] ?? null);
        $issuedAt = $this->stringValue($state['issued_at'] ?? null);
        $expiresAt = $this->stringValue($state['expires_at'] ?? null);

        if ($authorizationId === null
            || $whatsappNumber === null
            || $conversationId === null
            || $protectedAction === null
            || $status === null
            || $failedAttempts === null
            || $maxAttempts === null
            || $issuedAt === null
            || $expiresAt === null) {
            return null;
        }

        try {
            return new TemporaryAuthorizationEntity(
                authorizationId: $authorizationId,
                context: AuthorizationContext::forWhatsappConversation($whatsappNumber, $conversationId),
                protectedAction: $protectedAction,
                status: $status,
                authorizedUserId: $this->nullableStringValue($state['authorized_user_id'] ?? null),
                failedAttempts: $failedAttempts,
                maxAttempts: $maxAttempts,
                issuedAt: $issuedAt,
                expiresAt: $expiresAt,
                authorizedAt: $this->nullableStringValue($state['authorized_at'] ?? null),
                finishedAt: $this->nullableStringValue($state['finished_at'] ?? null),
            );
        } catch (Throwable) {
            return null;
        }
    }

    private function protectedAction(mixed $value): ?ProtectedActionEnum
    {
        $value = $this->stringValue($value);

        return $value === null ? null : ProtectedActionEnum::tryFrom($value);
    }

    private function status(mixed $value): ?TemporaryAuthorizationStatusEnum
    {
        $value = $this->stringValue($value);

        return $value === null ? null : TemporaryAuthorizationStatusEnum::tryFrom($value);
    }

    private function stringValue(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function nullableStringValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $this->stringValue($value);
    }

    private function integerValue(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }
}
