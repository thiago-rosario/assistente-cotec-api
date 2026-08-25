<?php

declare(strict_types=1);

namespace App\Contract\Application\Interfaces\Parser;

use DateTimeImmutable;

interface ContractDateParserInterface
{
    public function parse(mixed $value): ?DateTimeImmutable;
}
