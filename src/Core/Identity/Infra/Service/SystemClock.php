<?php

declare(strict_types=1);

namespace App\Core\Identity\Infra\Service;

use App\Core\Identity\Application\Interface\ClockInterface;
use DateTimeImmutable;

final class SystemClock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface(now());
    }
}
