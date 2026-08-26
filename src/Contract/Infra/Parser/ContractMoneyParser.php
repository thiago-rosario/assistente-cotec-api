<?php

declare(strict_types=1);

namespace App\Contract\Infra\Parser;

use App\Contract\Application\Interfaces\Parser\ContractMoneyParserInterface;
use App\Contract\Infra\Exception\ContractSheetRowMappingException;

class ContractMoneyParser implements ContractMoneyParserInterface
{
    public function parse(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (! is_scalar($value) && ! $value instanceof \Stringable) {
            throw new ContractSheetRowMappingException(
                message: 'Um valor monetário da planilha de contratos possui estrutura inválida.',
            );
        }

        $value = trim((string) $value);

        if ($value === '' || in_array($value, ['-', '/'], true)) {
            return null;
        }

        $value = str_replace(['R$', ' '], '', $value);
        $value = preg_replace('/(?<=\d)[,.]+$/u', '', $value) ?? $value;

        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        if (! is_numeric($value)) {
            throw new ContractSheetRowMappingException(
                message: 'Um valor monetário da planilha de contratos não pode ser convertido.',
            );
        }

        return (float) $value;
    }
}
