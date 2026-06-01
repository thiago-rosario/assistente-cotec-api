<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces;

use Illuminate\Support\Collection;

interface GoogleSheetRowMapperInterface
{
    public function mapRowsToHeader(array $header, Collection $rows): Collection;
}
