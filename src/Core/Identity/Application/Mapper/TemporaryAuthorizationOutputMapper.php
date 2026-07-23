<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Mapper;

use App\Core\Identity\Application\DTO\TemporaryAuthorizationOutputDTO;
use App\Core\Identity\Application\Interface\Mapper\TemporaryAuthorizationOutputMapperInterface;
use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;

class TemporaryAuthorizationOutputMapper implements TemporaryAuthorizationOutputMapperInterface
{
    public function map(TemporaryAuthorizationEntity $authorization): TemporaryAuthorizationOutputDTO
    {
        return new TemporaryAuthorizationOutputDTO(
            authorizationId: $authorization->authorizationId,
            context: $authorization->context,
            protectedAction: $authorization->protectedAction,
            status: $authorization->status,
            authorizedUserId: $authorization->authorizedUserId,
            failedAttempts: $authorization->failedAttempts,
            maxAttempts: $authorization->maxAttempts,
            remainingAttempts: $authorization->remainingAttempts(),
            issuedAt: $authorization->issuedAt,
            expiresAt: $authorization->expiresAt,
            authorizedAt: $authorization->authorizedAt,
            finishedAt: $authorization->finishedAt,
        );
    }
}
