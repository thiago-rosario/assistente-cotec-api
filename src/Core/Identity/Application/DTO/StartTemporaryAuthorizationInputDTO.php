<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\DTO;

use App\Core\Identity\Domain\ValueObject\AuthorizationContext;
use App\Core\Identity\Enum\ProtectedActionEnum;
use DateInterval;

readonly class StartTemporaryAuthorizationInputDTO
{
    public function __construct(
        public AuthorizationContext $context,
        public ProtectedActionEnum $protectedAction,
        public int $maxAttempts = 3,
        public ?DateInterval $timeToLive = null,
        public ?string $authorizationId = null,
    ) {}
}
