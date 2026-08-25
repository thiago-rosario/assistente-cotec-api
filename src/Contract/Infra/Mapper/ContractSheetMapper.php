<?php

declare(strict_types=1);

namespace App\Contract\Infra\Mapper;

use App\Contract\Application\Interfaces\Mapper\ContractSheetMapperInterface;
use App\Contract\Application\Interfaces\Parser\ContractDateParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractMoneyParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractNullableStringParserInterface;
use App\Contract\Application\Interfaces\Parser\ContractNumberParserInterface;
use App\Contract\Domain\Entity\ContractEntity;

class ContractSheetMapper implements ContractSheetMapperInterface
{
    public function __construct(
        private readonly ContractNumberParserInterface $contractNumberParser,
        private readonly ContractNullableStringParserInterface $nullableStringParser,
        private readonly ContractDateParserInterface $dateParser,
        private readonly ContractMoneyParserInterface $moneyParser,
    ) {}

    public function map(array $row): ContractEntity
    {
        $municipality = $this->nullableStringParser->parse($row['MUNICÍPIO'] ?? null);

        return new ContractEntity(
            contractNumber: $this->contractNumberParser->parse(
                $row['Nº DO CT']
                    ?? $row['N° DO CT']
                    ?? $row['N° DO CONTRATO']
                    ?? $row['CONTRATO']
                    ?? null,
            ),
            company: $this->nullableStringParser->parse($row['EMPRESA'] ?? null),
            seiProcess: $this->nullableStringParser->parse(
                $row['PROCESSO SEI Nº']
                    ?? $row['N° PROCESSO SEI']
                    ?? $row['PROCESSO SEI']
                    ?? null,
            ),
            municipalities: $municipality === null ? [] : [$municipality],
            object: $this->nullableStringParser->parse($row['OBJETO'] ?? null),
            initialValue: $this->moneyParser->parse($row['VALOR INICIAL'] ?? null),
            updatedValue: $this->moneyParser->parse(
                $row['VALOR ATUALIZADO']
                    ?? $row['VALOR APÓS PUBLICAÇÃO']
                    ?? null,
            ),
            validityStartDate: $this->dateParser->parse($row['VIGÊNCIA INÍCIO'] ?? null),
            validityEndDate: $this->dateParser->parse($row['VIGÊNCIA FINAL'] ?? null),
            executionDeadline: $this->nullableStringParser->parse(
                $row['EXECUÇÃO FINAL']
                    ?? $row['PRAZO DE EXECUÇÃO']
                    ?? null,
            ),
            currentSituation: $this->nullableStringParser->parse(
                $row['SITUAÇÃO']
                    ?? $row['SITUAÇÃO DO CONTRATO']
                    ?? $row['STATUS']
                    ?? null,
            ),
        );
    }
}
