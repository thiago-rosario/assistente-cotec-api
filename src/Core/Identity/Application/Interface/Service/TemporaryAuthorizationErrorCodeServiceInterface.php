<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Interface\Service;

use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;
use App\Core\Identity\Enum\IdentityCodeExceptionEnum;

interface TemporaryAuthorizationErrorCodeServiceInterface
{
    public function resolve(TemporaryAuthorizationEntity $authorization): IdentityCodeExceptionEnum;

    public function resolveForExecution(TemporaryAuthorizationEntity $authorization): IdentityCodeExceptionEnum;
}
