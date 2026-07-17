<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Trait;

use DateTimeImmutable;
use DateTimeInterface;
use LogicException;

trait MethodsMagicsTrait
{
    public function __get(string $property): mixed
    {
        if (! property_exists($this, $property)) {
            throw new LogicException(sprintf(
                'A propriedade [%s] não existe em [%s].',
                $property,
                static::class,
            ));
        }

        return $this->{$property};
    }

    public function __isset(string $property): bool
    {
        return property_exists($this, $property) && isset($this->{$property});
    }

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
