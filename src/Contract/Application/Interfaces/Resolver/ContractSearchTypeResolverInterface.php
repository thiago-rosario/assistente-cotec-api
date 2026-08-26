<?php

declare(strict_types=1);

namespace App\Contract\Application\Interfaces\Resolver;

use App\Contract\Enum\ContractSearchTypeEnum;

interface ContractSearchTypeResolverInterface
{
    public function resolve(string $searchTerm): ?ContractSearchTypeEnum;
}
