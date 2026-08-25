<?php

use App\Contract\Application\Service\ContractRemainingDaysCalculatorService;

it('calculates deterministic remaining days including expired deadlines', function () {
    $calculator = new ContractRemainingDaysCalculatorService;
    $referenceDate = new DateTimeImmutable('2026-08-24');

    expect($calculator->calculate(new DateTimeImmutable('2026-08-31'), $referenceDate))->toBe(7)
        ->and($calculator->calculate(new DateTimeImmutable('2026-08-23'), $referenceDate))->toBe(-1)
        ->and($calculator->calculate(new DateTimeImmutable('2026-08-24'), $referenceDate))->toBe(0)
        ->and($calculator->calculate(null, $referenceDate))->toBeNull();
});
