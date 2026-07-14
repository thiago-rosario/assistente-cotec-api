<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Application\Trait;

trait NullableResultTrait
{
    /**
     * @return list<object>
     */
    private function nullableResult(?object $result): array
    {
        if ($result === null) {
            return [];
        }

        return [$result];
    }
}
