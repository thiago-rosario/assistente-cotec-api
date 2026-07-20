<?php

declare(strict_types=1);

namespace App\Core\Identity\Domain\Trait;

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
}
