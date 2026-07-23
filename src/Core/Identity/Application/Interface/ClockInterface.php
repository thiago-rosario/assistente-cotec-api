<?php

declare(strict_types=1);

namespace App\Core\Identity\Application\Interface;

use DateTimeImmutable;

interface ClockInterface
{
    public function now(): DateTimeImmutable;
}
