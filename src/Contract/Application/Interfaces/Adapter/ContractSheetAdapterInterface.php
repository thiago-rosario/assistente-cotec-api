<?php

declare(strict_types=1);

namespace App\Contract\Application\Interfaces\Adapter;

interface ContractSheetAdapterInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function read(string $sheetKey): array;

    /**
     * @template T
     *
     * @param  callable(array<string, mixed>): T  $mapper
     * @return list<T>
     */
    public function map(string $sheetKey, callable $mapper): array;
}
