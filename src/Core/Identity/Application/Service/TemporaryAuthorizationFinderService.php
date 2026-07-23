<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Service;

use App\Core\Identity\Application\Interface\Service\TemporaryAuthorizationFinderServiceInterface;
use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;
use App\Core\Identity\Domain\Repository\TemporaryAuthorizationRepositoryInterface;
use App\Core\Identity\Exception\TemporaryAuthorizationNotFoundException;

class TemporaryAuthorizationFinderService implements TemporaryAuthorizationFinderServiceInterface
{
    public function __construct(
        private readonly TemporaryAuthorizationRepositoryInterface $repository
    ) {}

    public function findOrFail(string $authorizationId): TemporaryAuthorizationEntity
    {
        return $this->repository->findById($authorizationId)
            ?? throw new TemporaryAuthorizationNotFoundException;
    }
}
