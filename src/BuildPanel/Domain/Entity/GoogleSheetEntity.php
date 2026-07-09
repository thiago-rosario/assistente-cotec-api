<?php

declare(strict_types=1);

namespace App\BuildPanel\Domain\Entity;

use App\BuildPanel\Exception\GoogleSheetGidInvalidException;
use App\BuildPanel\Exception\GoogleSheetNameRequiredException;
use App\BuildPanel\Exception\GoogleSpreadsheetIdRequiredException;
use InvalidArgumentException;

class GoogleSheetEntity
{
    public function __construct(
        public string $spreadsheetId,
        public int $gid,
        public string $name,
    ) {
        if (trim($this->spreadsheetId) === '') {
            throw new GoogleSpreadsheetIdRequiredException;
        }

        if ($this->gid <= 0) {
            throw new GoogleSheetGidInvalidException;
        }

        if (trim($this->name) === '') {
            throw new GoogleSheetNameRequiredException;
        }
    }

    /**
     * @param  array<int, string>  $sheets
     * @return array<int, self>
     */
    public static function fromConfiguredSheets(string $spreadsheetId, array $sheets): array
    {
        if ($sheets === []) {
            throw new InvalidArgumentException('A planilha Google deve possuir ao menos uma aba configurada.');
        }

        self::ensureUniqueGids(array_keys($sheets));
        self::ensureUniqueNames(array_values($sheets));

        $entities = [];

        foreach ($sheets as $gid => $sheetName) {
            $entities[] = new self(
                spreadsheetId: $spreadsheetId,
                gid: $gid,
                name: $sheetName,
            );
        }

        return $entities;
    }

    public function quotedRangeName(): string
    {
        return "'".str_replace("'", "''", $this->name)."'";
    }

    /**
     * @return array{gid: int, name: string}
     */
    public function toDiagnosticArray(): array
    {
        return [
            'gid' => $this->gid,
            'name' => $this->name,
        ];
    }

    /**
     * @param  array<int, int>  $gids
     */
    private static function ensureUniqueGids(array $gids): void
    {
        if (count($gids) !== count(array_unique($gids))) {
            throw new InvalidArgumentException('As abas da planilha Google devem possuir gids únicos.');
        }
    }

    /**
     * @param  array<int, string>  $names
     */
    private static function ensureUniqueNames(array $names): void
    {
        $normalizedNames = array_map(
            fn (string $name): string => mb_strtolower(trim($name)),
            $names,
        );

        if (count($normalizedNames) !== count(array_unique($normalizedNames))) {
            throw new InvalidArgumentException('As abas da planilha Google devem possuir nomes únicos.');
        }
    }
}
