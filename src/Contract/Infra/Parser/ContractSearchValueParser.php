<?php

declare(strict_types=1);

namespace App\Contract\Infra\Parser;

use App\Contract\Application\Interfaces\Parser\ContractSearchValueParserInterface;
use Illuminate\Support\Str;

class ContractSearchValueParser implements ContractSearchValueParserInterface
{
    public function parse(mixed $value): string
    {
        return Str::of((string) $value)
            ->trim()
            ->lower()
            ->ascii()
            ->replaceMatches('/\s+/', ' ')
            ->toString();
    }
}
