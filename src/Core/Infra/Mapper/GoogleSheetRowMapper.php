<?php

declare(strict_types=1);

namespace App\Core\Infra\Mapper;

use App\Core\Application\Interfaces\GoogleSheetRowMapperInterface;
use Illuminate\Support\Collection;

class GoogleSheetRowMapper implements GoogleSheetRowMapperInterface
{
    public function mapRowsToHeader(array $header, Collection $rows): Collection
    {
        if ($header === []) {
            return collect();
        }

        return $rows->map(function (array $row) use ($header): array {
            return collect($header)
                ->combine(
                    collect($row)
                        ->take(count($header))
                        ->pad(count($header), '')
                )
                ->all();
        });
    }
}
