<?php

declare(strict_types=1);

namespace App\Core\Infra\Mapper;

use App\Core\Application\Interfaces\LandSurveySheetMapperInterface;
use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Infra\Trait\CastsSpreadsheetValues;

class LandSurveySheetMapper implements LandSurveySheetMapperInterface
{
    use CastsSpreadsheetValues;

    public function fromRow(array $row): LandSurveyEntity
    {
        return new LandSurveyEntity(
            municipality: $this->toString($row['MUNICIPIO'] ?? $row['MUNICÍPIO'] ?? '') ?? '',
            process: $this->toString($row['PROCESSO'] ?? null),
            region: $this->toString($row['REGIÃO'] ?? $row['REGIAO'] ?? null),
            unitSizeClaim: $this->toString($row['PLEITO'] ?? $row['TIPOLOGIA'] ?? null),
            force: $this->toString($row['FORÇA'] ?? null),
            requester: $this->toString($row['SOLICITANTE'] ?? $row['DEMANDANTE'] ?? null),
            ownership: $this->toString($row['TITULARIDADE'] ?? $row['PROPRIEDADE'] ?? null),
            topography: $this->toString($row['TOPOGRAFIA'] ?? $row['LEVANTAMENTO TOPOGRÁFICO'] ?? null),
            landStatus: $this->toString($row['STATUS DO TERRENO'] ?? $row['SITUAÇÃO DO TERRENO'] ?? null),
            progress: $this->toString($row['ANDAMENTO'] ?? $row['PROGRESSO'] ?? null),
            municipalityFocalPointContact: $this->toString($row['CONTATO MUNICÍPIO'] ?? $row['PONTO FOCAL MUNICÍPIO'] ?? null),
            militaryPoliceFocalPointContact: $this->toString($row['CONTATO PM'] ?? $row['PONTO FOCAL PM'] ?? null),
            civilPoliceFocalPointContact: $this->toString($row['CONTATO PC'] ?? $row['PONTO FOCAL PC'] ?? null),
            documentationLink: $this->toString($row['LINK DOCUMENTAÇÃO'] ?? $row['DOCUMENTAÇÃO'] ?? null),
            updatedAt: $this->toDateTimeImmutable($row['ATUALIZADO EM'] ?? $row['DATA DE ATUALIZAÇÃO'] ?? null),
            observations: $this->toString($row['OBSERVAÇÕES'] ?? $row['OBSERVACOES'] ?? null),
            requestedAt: $this->toDateTimeImmutable($row['SOLICITADO EM'] ?? $row['DATA DE SOLICITAÇÃO'] ?? null),
        );
    }
}
