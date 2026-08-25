<?php

declare(strict_types=1);

namespace App\Contract\Infra\Parser;

use App\Contract\Application\Interfaces\Parser\ContractIntegerParserInterface;
use App\Contract\Infra\Exception\ContractSheetRowMappingException;

class ContractIntegerParser implements ContractIntegerParserInterface
{
    public function parse(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (! is_scalar($value) && ! $value instanceof \Stringable) {
            throw new ContractSheetRowMappingException(
                message: 'Um número inteiro da planilha de contratos possui estrutura inválida.',
            );
        }

        $value = trim((string) $value);

        if ($value === '' || in_array($value, ['-', '/'], true)) {
            return null;
        }

        if (! is_numeric($value)) {
            throw new ContractSheetRowMappingException(
                message: 'Um número inteiro da planilha de contratos não pode ser convertido.',
            );
        }

        return (int) $value;
    }
}
