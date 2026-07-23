<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Interface\Service;

use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;

interface TemporaryAuthorizationFinderServiceInterface
{
    public function findOrFail(string $authorizationId): TemporaryAuthorizationEntity;
}
