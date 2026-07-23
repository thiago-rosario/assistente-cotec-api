<?php

declare(strict_types=1);

namespace App\Core\Identity\Infra\Repository\Cache;

use App\Core\Identity\Domain\ValueObject\AuthorizationContext;
use App\Core\Identity\Enum\ProtectedActionEnum;

final class TemporaryAuthorizationCacheKeyResolver
{
    private const string AuthorizationCachePrefix = 'identity:temporary-authorization:';

    private const string ContextCachePrefix = 'identity:temporary-authorization-context:';

    public function authorizationKey(string $authorizationId): string
    {
        return self::AuthorizationCachePrefix.hash('sha256', trim($authorizationId));
    }

    public function contextKey(AuthorizationContext $context, ProtectedActionEnum $protectedAction): string
    {
        return self::ContextCachePrefix.hash('sha256', implode('|', [
            $context->whatsappNumber,
            $context->conversationId,
            $protectedAction->value,
        ]));
    }
}
