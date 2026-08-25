<?php

declare(strict_types=1);

namespace App\Contract\Infra\Mapper;

use App\Contract\Application\Interfaces\Mapper\ContractExecutionDeadlineSheetMapperInterface;
use App\Contract\Application\Interfaces\Parser\ContractDateParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractIntegerParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractNullableStringParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractNumberParserInterface;
use App\Contract\Domain\Entity\ContractExecutionDeadlineEntity;

class ContractExecutionDeadlineSheetMapper implements ContractExecutionDeadlineSheetMapperInterface
{
    public function __construct(
        private readonly ContractNumberParserInterface $contractNumberParser,
        private readonly ContractNullableStringParserInterface $nullableStringParser,
        private readonly ContractDateParserInterface $dateParser,
        private readonly ContractIntegerParserInterface $integerParser,
    ) {}

    public function map(array $row): ContractExecutionDeadlineEntity
    {
        return new ContractExecutionDeadlineEntity(
            contractNumber: $this->contractNumberParser->parse($row['CONTRATO'] ?? null),
            company: $this->nullableStringParser->parse($row['EMPRESA'] ?? null),
            municipality: $this->nullableStringParser->parse($row['MUNICÍPIO'] ?? null),
            seiProcess: $this->nullableStringParser->parse($row['N° DO PROCESSO SEI'] ?? null),
            validityEndDate: $this->dateParser->parse($row['FINAL DA VIGÊNCIA'] ?? null),
            executionEndDate: $this->dateParser->parse($row['FINAL DA EXECUÇÃO'] ?? null),
            contractSituation: $this->nullableStringParser->parse($row['SITUAÇÃO DO CONTRATO'] ?? null),
            deadlineAddendumStatus: $this->nullableStringParser->parse($row['STATUS ADITIVO  PRAZO'] ?? null),
            location: $this->nullableStringParser->parse($row['LOCAL'] ?? null),
            publicationDate: $this->dateParser->parse($row['DATA PUBLICAÇÃO'] ?? null),
            observation: $this->nullableStringParser->parse($row['OBS:'] ?? null),
            entryDate: $this->dateParser->parse($row['DATA DE ENTRADA'] ?? null),
            processingTimeDays: $this->integerParser->parse($row['TEMPO DE TRAMITAÇÃO'] ?? null),
            publicationTimeDays: $this->integerParser->parse($row['TEMPO DE PUBLICAÇÃO'] ?? null),
            unit: $this->nullableStringParser->parse($row['UNIDADE'] ?? null),
        );
    }
}
