<?php

declare(strict_types=1);

namespace App\Core\Identity\Domain\Trait;

use DateTimeImmutable;
use DateTimeInterface;

trait DateTimeConversionTrait
{
    private static function dateTime(DateTimeInterface|string|null $dateTime): DateTimeImmutable
    {
        if ($dateTime instanceof DateTimeImmutable) {
            return $dateTime;
        }

        if ($dateTime instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($dateTime);
        }

        return new DateTimeImmutable($dateTime ?: 'now');
    }

    private static function nullableDateTime(DateTimeInterface|string|null $dateTime): ?DateTimeImmutable
    {
        if ($dateTime === null || $dateTime === '') {
            return null;
        }

        return self::dateTime($dateTime);
    }
}
