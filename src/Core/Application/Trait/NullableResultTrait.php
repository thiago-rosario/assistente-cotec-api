<?php

namespace App\Core\Application\Trait;

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
