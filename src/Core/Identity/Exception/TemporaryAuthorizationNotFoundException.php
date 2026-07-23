<?php

declare(strict_types=1);

namespace App\Core\Identity\Exception;

use App\Core\Identity\Enum\IdentityCodeExceptionEnum;
use Throwable;

class TemporaryAuthorizationNotFoundException extends IdentityApplicationException
{
    public function __construct(
        ?string $message = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            identityCode: IdentityCodeExceptionEnum::TemporaryAuthorizationNotFound,
            message: $message,
            previous: $previous,
        );
    }
}
