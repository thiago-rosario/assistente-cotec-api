<?php

declare(strict_types=1);

namespace App\Contract\Application\Interfaces\Parser;

interface ContractIntegerParserInterface
{
    public function parse(mixed $value): ?int;
}
