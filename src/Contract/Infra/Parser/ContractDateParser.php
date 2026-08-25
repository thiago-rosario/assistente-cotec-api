<?php

declare(strict_types=1);

namespace App\Contract\Infra\Parser;

use App\Contract\Application\Interfaces\Parser\ContractDateParserInterface;
use App\Contract\Infra\Exception\ContractSheetRowMappingException;
use DateTimeImmutable;
use Throwable;

class ContractDateParser implements ContractDateParserInterface
{
    public function parse(mixed $value): ?DateTimeImmutable
    {
        if ($value === null || $value instanceof DateTimeImmutable) {
            return $value;
        }

        if (! is_scalar($value) && ! $value instanceof \Stringable) {
            throw new ContractSheetRowMappingException(
                message: 'Um valor de data da planilha de contratos possui estrutura inválida.',
            );
        }

        $value = trim((string) $value);

        if ($value === '' || in_array($value, ['-', '/'], true)) {
            return null;
        }

        foreach (['!d/m/Y', '!j/n/Y', '!d-n-Y', '!Y-m-d', '!n/j/Y'] as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value);
            $errors = DateTimeImmutable::getLastErrors();

            if ($date instanceof DateTimeImmutable
                && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $date;
            }
        }

        if (is_numeric($value)) {
            try {
                return new DateTimeImmutable('1899-12-30 +'.((int) $value).' days');
            } catch (Throwable $throwable) {
                throw new ContractSheetRowMappingException(
                    message: 'Um número de data da planilha de contratos não pode ser convertido.',
                    previous: $throwable,
                );
            }
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Throwable $throwable) {
            throw new ContractSheetRowMappingException(
                message: 'Uma data da planilha de contratos não pode ser convertida.',
                previous: $throwable,
            );
        }
    }
}
