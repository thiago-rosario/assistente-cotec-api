<?php

declare(strict_types=1);

namespace App\Contract\Infra\Mapper;

use App\Contract\Application\Interfaces\Mapper\ValueAdditiveSheetMapperInterface;
use App\Contract\Application\Interfaces\Parser\ContractDateParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractIntegerParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractMoneyParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractNullableStringParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractNumberParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractRequiredStringParserInterface;
use App\Contract\Domain\Entity\ValueAdditiveEntity;

class ValueAdditiveSheetMapper implements ValueAdditiveSheetMapperInterface
{
    public function __construct(
        private readonly ContractNumberParserInterface $contractNumberParser,
        private readonly ContractRequiredStringParserInterface $requiredStringParser,
        private readonly ContractNullableStringParserInterface $nullableStringParser,
        private readonly ContractDateParserInterface $dateParser,
        private readonly ContractIntegerParserInterface $integerParser,
        private readonly ContractMoneyParserInterface $moneyParser,
    ) {}

    public function map(array $row): ValueAdditiveEntity
    {
        return new ValueAdditiveEntity(
            contractNumber: $this->contractNumberParser->parse($row['N° DO CONTRATO'] ?? null),
            municipality: $this->requiredStringParser->parse($row['MUNICÍPIO'] ?? null),
            company: $this->nullableStringParser->parse($row['EMPRESA'] ?? null),
            seiProcess: $this->nullableStringParser->parse($row['N° PROCESSO SEI'] ?? null),
            stage: $this->nullableStringParser->parse($row['ETAPA'] ?? null),
            unit: $this->nullableStringParser->parse($row['UNIDADE'] ?? null),
            type: $this->nullableStringParser->parse($row['TIPO'] ?? null),
            value: $this->moneyParser->parse($row['VALOR'] ?? null),
            status: $this->nullableStringParser->parse($row['STATUS'] ?? null),
            currentLocation: $this->nullableStringParser->parse($row['LOCAL ATUAL'] ?? null),
            situation: $this->nullableStringParser->parse($row['SITUAÇÃO'] ?? null),
            publicationDate: $this->dateParser->parse($row['DATA DA PUBLICAÇÃO'] ?? null)?->format('Y-m-d'),
            publishedValue: $this->moneyParser->parse($row['VALOR APÓS PUBLICAÇÃO'] ?? null),
            additiveNumber: $this->nullableStringParser->parse($row['N° DO ADITIVO'] ?? null),
            observation: $this->nullableStringParser->parse($row['OBS:'] ?? null),
            entryDate: $this->dateParser->parse($row['DATA DA ENTRADA NO PROTOCOLO'] ?? null)?->format('Y-m-d'),
            processingTimeDays: $this->integerParser->parse($row['TEMPO DE TRAMITAÇÃO'] ?? null),
            publicationTimeDays: $this->integerParser->parse($row['TEMPO PUBLICAÇÃO'] ?? null),
        );
    }
}
