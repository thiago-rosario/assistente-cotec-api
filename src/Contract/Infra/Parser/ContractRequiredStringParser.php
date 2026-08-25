<?php

declare(strict_types=1);

namespace App\Contract\Infra\Parser;

use App\Contract\Application\Interfaces\Parser\ContractNullableStringParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractRequiredStringParserInterface;
use App\Contract\Infra\Exception\ContractSheetRowMappingException;

class ContractRequiredStringParser implements ContractRequiredStringParserInterface
{
    public function __construct(
        private readonly ContractNullableStringParserInterface $nullableStringParser,
    ) {}

    public function parse(mixed $value): string
    {
        $parsedValue = $this->nullableStringParser->parse($value);

        if ($parsedValue === null) {
            throw new ContractSheetRowMappingException(
                message: 'Um campo obrigatório da planilha de contratos está vazio.',
            );
        }

        return $parsedValue;
    }
}
