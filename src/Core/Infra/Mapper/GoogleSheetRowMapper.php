<?php

declare(strict_types=1);

namespace App\Core\Infra\Mapper;

use App\Core\Application\Interfaces\Mapper\GoogleSheetRowMapperInterface;
use Illuminate\Support\Collection;

class GoogleSheetRowMapper implements GoogleSheetRowMapperInterface
{
    public function mapRowsToHeader(array $header, Collection $rows): Collection
    {
        if ($header === []) {
            return collect();
        }

        [$resolvedHeader, $resolvedRows] = $this->resolveHeaderAndRows($header, $rows);

        return $resolvedRows->map(function (array $row) use ($resolvedHeader): array {
            return collect($resolvedHeader)
                ->combine(
                    collect($row)
                        ->take(count($resolvedHeader))
                        ->pad(count($resolvedHeader), '')
                )
                ->all();
        });
    }

    /**
     * @return array{0: array<int, mixed>, 1: Collection<int, array<int, mixed>>}
     */
    private function resolveHeaderAndRows(array $header, Collection $rows): array
    {
        $candidateHeader = $rows->first();

        if (
            is_array($candidateHeader)
            && $this->hasWeakHeader($header)
            && $this->headerScore($candidateHeader) > $this->headerScore($header)
        ) {
            return [$candidateHeader, $rows->skip(1)->values()];
        }

        return [$header, $rows];
    }

    private function hasWeakHeader(array $header): bool
    {
        $labels = collect($header)
            ->map(fn (mixed $value): string => trim((string) $value));

        return $labels->contains('')
            || $labels->unique()->count() !== $labels->count();
    }

    private function headerScore(array $header): int
    {
        return collect($header)
            ->map(fn (mixed $value): string => trim((string) $value))
            ->filter()
            ->unique()
            ->count();
    }
}
