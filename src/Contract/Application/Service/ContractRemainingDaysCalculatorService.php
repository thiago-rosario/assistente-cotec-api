<?php

declare(strict_types=1);

namespace App\Contract\Application\Service;

use App\Contract\Application\Interfaces\Service\ContractRemainingDaysCalculatorServiceInterface;
use DateTimeImmutable;

class ContractRemainingDaysCalculatorService implements ContractRemainingDaysCalculatorServiceInterface
{
    public function calculate(?DateTimeImmutable $endDate, DateTimeImmutable $referenceDate): ?int
    {
        if ($endDate === null) {
            return null;
        }

        $interval = $referenceDate->setTime(0, 0)->diff($endDate->setTime(0, 0));

        return $interval->invert === 1 ? -$interval->days : $interval->days;
    }
}
