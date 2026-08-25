<?php

declare(strict_types=1);

namespace App\Contract\Application\Interfaces\Parser;

interface ContractMoneyParserInterface
{
    public function parse(mixed $value): ?float;
}
