<?php

declare(strict_types=1);

namespace App\Contract\Application\Interfaces\Parser;

interface ContractSearchValueParserInterface
{
    public function parse(mixed $value): string;
}
