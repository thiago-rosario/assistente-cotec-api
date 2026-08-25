<?php

declare(strict_types=1);

namespace App\Contract\Application\Interfaces\Service;

use DateTimeImmutable;

interface ContractRemainingDaysCalculatorServiceInterface
{
    public function calculate(?DateTimeImmutable $endDate, DateTimeImmutable $referenceDate): ?int;
}
