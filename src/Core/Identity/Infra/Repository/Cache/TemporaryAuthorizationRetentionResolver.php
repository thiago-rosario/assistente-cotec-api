<?php

declare(strict_types=1);

namespace App\Core\Identity\Infra\Repository\Cache;

use App\Core\Identity\Domain\Entity\TemporaryAuthorizationEntity;
use DateTimeImmutable;

final class TemporaryAuthorizationRetentionResolver
{
    public function resolve(TemporaryAuthorizationEntity $authorization): DateTimeImmutable
    {
        $retentionUntil = $authorization->expiresAt->modify('+1 hour');
        $issuedRetentionUntil = $authorization->issuedAt->modify('+8 hours');

        if ($issuedRetentionUntil > $retentionUntil) {
            $retentionUntil = $issuedRetentionUntil;
        }

        if ($authorization->finishedAt === null) {
            return $retentionUntil;
        }

        $finishedRetentionUntil = $authorization->finishedAt->modify('+8 hours');

        return $finishedRetentionUntil > $retentionUntil
            ? $finishedRetentionUntil
            : $retentionUntil;
    }
}
