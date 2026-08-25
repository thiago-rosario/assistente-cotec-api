<?php

declare(strict_types=1);

namespace App\Contract\Infra\Parser;

use App\Contract\Application\Interfaces\Parser\ContractNullableStringParserInterface;
use App\Contract\Infra\Exception\ContractSheetRowMappingException;

class ContractNullableStringParser implements ContractNullableStringParserInterface
{
    public function parse(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! is_scalar($value) && ! $value instanceof \Stringable) {
            throw new ContractSheetRowMappingException(
                message: 'Um valor textual da planilha de contratos possui estrutura inválida.',
            );
        }

        $value = trim((string) $value);

        return $value === '' || in_array($value, ['-', '/'], true) ? null : $value;
    }
}
