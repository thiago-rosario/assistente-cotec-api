<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\DTO;

use App\Core\Identity\Domain\ValueObject\AuthorizationContext;
use App\Core\Identity\Enum\ProtectedActionEnum;

readonly class ValidateTemporaryAuthorizationInputDTO
{
    public function __construct(
        public string $authorizationId,
        public AuthorizationContext $context,
        public ProtectedActionEnum $protectedAction,
    ) {}
}
