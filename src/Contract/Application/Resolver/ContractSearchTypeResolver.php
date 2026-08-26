<?php

declare(strict_types=1);

namespace App\Contract\Application\Resolver;

use App\Contract\Application\Interfaces\Resolver\ContractSearchTypeResolverInterface;
use App\Contract\Enum\ContractSearchTypeEnum;
use Illuminate\Support\Str;

class ContractSearchTypeResolver implements ContractSearchTypeResolverInterface
{
    private const string ContractNumberPattern = '/^\d+\s*\/\s*\d{4}$/u';

    private const string CompanyPattern = '/\b(?:empresa|eng(?:enharia)?|construtora|cons[oó]rcio|ltda|incorporadora|s\.?a\.?|s\/a)\b/iu';

    public function resolve(string $searchTerm): ?ContractSearchTypeEnum
    {
        $searchTerm = trim($searchTerm);

        if ($searchTerm === '') {
            return null;
        }

        if (preg_match(self::ContractNumberPattern, $searchTerm) === 1) {
            return ContractSearchTypeEnum::ContractNumber;
        }

        if (preg_match(self::CompanyPattern, $searchTerm) === 1) {
            return ContractSearchTypeEnum::Company;
        }

        $normalizedSearchTerm = Str::of($searchTerm)
            ->ascii()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();

        return preg_match('/^[\p{L}\s\'-]{3,100}$/u', $normalizedSearchTerm) === 1
            ? ContractSearchTypeEnum::Municipality
            : null;
    }
}
