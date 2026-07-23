<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Interface\Mapper;

use App\Core\Identity\Application\DTO\TemporaryAuthorizationOutputDTO;
use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;

interface TemporaryAuthorizationOutputMapperInterface
{
    public function map(TemporaryAuthorizationEntity $authorization): TemporaryAuthorizationOutputDTO;
}
