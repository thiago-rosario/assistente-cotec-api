<?php

declare(strict_types=1);

namespace App\Contract\Infra\Mapper;

use App\Contract\Application\Interfaces\Mapper\ContractReadjustmentSheetMapperInterface;
use App\Contract\Application\Interfaces\Parser\ContractDateParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractIntegerParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractMoneyParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractNullableStringParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractNumberParserInterface;
use App\Contract\Domain\Entity\ContractReadjustmentEntity;

class ContractReadjustmentSheetMapper implements ContractReadjustmentSheetMapperInterface
{
    public function __construct(
        private readonly ContractNumberParserInterface $contractNumberParser,
        private readonly ContractNullableStringParserInterface $nullableStringParser,
        private readonly ContractDateParserInterface $dateParser,
        private readonly ContractIntegerParserInterface $integerParser,
        private readonly ContractMoneyParserInterface $moneyParser,
    ) {}

    public function map(array $row): ContractReadjustmentEntity
    {
        return new ContractReadjustmentEntity(
            entryDate: $this->dateParser->parse($row['DATA DE INGRESSO'] ?? $row[' DATA DE INGRESSO'] ?? null),
            company: $this->nullableStringParser->parse($row['EMPRESA'] ?? null),
            ceirfEntryDate: $this->dateParser->parse($row['ENTRADA NA CEIRF'] ?? null),
            ceirfLastMovementDate: $this->dateParser->parse($row['ÚLTIMA MOVIMENTAÇÃO NA CEIRF'] ?? null),
            contractNumber: $this->contractNumberParser->parse($row['N° DO CONTRATO'] ?? null),
            seiProcess: $this->nullableStringParser->parse($row['PROCESSO SEI'] ?? null),
            apostilleNumber: $this->nullableStringParser->parse($row['N° DA APOSTILA'] ?? null),
            contemplatedValue: $this->moneyParser->parse($row['VALOR CONTEMPLADO'] ?? null),
            contemplatedIncidencePeriod: $this->nullableStringParser->parse(
                $row['PERÍODO DE INCIDÊNCIA CONTEMPLADO'] ?? null,
            ),
            status: $this->nullableStringParser->parse($row['STATUS'] ?? null),
            location: $this->nullableStringParser->parse($row['LOCAL'] ?? null),
            processingTimeDays: $this->integerParser->parse($row['TEMPO DE TRAMITAÇÃO'] ?? null),
            publicationDate: $this->dateParser->parse($row['DATA PUBLICAÇÃO'] ?? null),
            publicationTimeDays: $this->integerParser->parse($row['TEMPO PUBLICAÇÃO'] ?? null),
            observation: $this->nullableStringParser->parse($row['OBS:'] ?? null),
            paymentSituation: $this->nullableStringParser->parse($row['SITUAÇÃO DO PAGAMENTO'] ?? null),
            paymentSei: $this->nullableStringParser->parse($row['SEI DE PAGAMENTO'] ?? null),
        );
    }
}
