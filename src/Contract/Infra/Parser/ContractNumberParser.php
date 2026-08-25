<?php

declare(strict_types=1);

namespace App\Contract\Infra\Parser;

use App\Contract\Application\Interfaces\Parser\ContractNullableStringParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractNumberParserInterface;
use App\Contract\Infra\Exception\ContractSheetRowMappingException;

class ContractNumberParser implements ContractNumberParserInterface
{
    public function __construct(
        private readonly ContractNullableStringParserInterface $nullableStringParser,
    ) {}

    public function parse(mixed $value): string
    {
        $parsedValue = $this->nullableStringParser->parse($value);

        if ($parsedValue === null) {
            throw new ContractSheetRowMappingException(
                message: 'O número do contrato não pode estar vazio na planilha de contratos.',
            );
        }

        return preg_replace('/\s*\/\s*/u', '/', $parsedValue) ?? $parsedValue;
    }
}
