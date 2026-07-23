<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\DTO;

use App\Core\Identity\Domain\ValueObject\AuthorizationContext;
use App\Core\Identity\Enum\ProtectedActionEnum;
use App\Core\Identity\Enum\TemporaryAuthorizationStatusEnum;
use DateTimeImmutable;

readonly class TemporaryAuthorizationOutputDTO
{
    public function __construct(
        public string $authorizationId,
        public AuthorizationContext $context,
        public ProtectedActionEnum $protectedAction,
        public TemporaryAuthorizationStatusEnum $status,
        public ?string $authorizedUserId,
        public int $failedAttempts,
        public int $maxAttempts,
        public int $remainingAttempts,
        public DateTimeImmutable $issuedAt,
        public DateTimeImmutable $expiresAt,
        public ?DateTimeImmutable $authorizedAt,
        public ?DateTimeImmutable $finishedAt,
    ) {}
}
