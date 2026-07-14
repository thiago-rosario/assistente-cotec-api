<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Infra\Trait;

trait SearchableEntityMatcherTrait
{
    /**
     * @param  object{toSearchableArray: callable(): array<int, string|null>}  $entity
     */
    private function matchesSearchTerm(object $entity, string $normalizedTerm): bool
    {
        foreach ($entity->toSearchableArray() as $value) {
            if ($value !== null && str_contains($this->normalize($value), $normalizedTerm)) {
                return true;
            }
        }

        return false;
    }
}
