<?php

declare(strict_types=1);

namespace App\Core\Infra\Mapper;

use App\Core\Application\Interfaces\ConstructionDemandSheetMapperInterface;
use App\Core\Domain\Entity\ConstructionDemandEntity;
use App\Core\Infra\Trait\CastsSpreadsheetValues;

class ConstructionDemandSheetMapper implements ConstructionDemandSheetMapperInterface
{
    use CastsSpreadsheetValues;

    public function fromRow(array $row): ConstructionDemandEntity
    {
        return new ConstructionDemandEntity(
            municipality: $this->toString($row['MUNICIPIO'] ?? $row['MUNICÍPIO'] ?? '') ?? '',
            force: $this->toString($row['FORÇA'] ?? null),
            process: $this->toString($row['PROCESSO'] ?? null),
            unitClaim: $this->toString($row['PLEITO'] ?? $row['PLEITO UNIDADE'] ?? null),
            requesterDescription: $this->toString($row['DESCRIÇÃO DO SOLICITANTE'] ?? $row['DESCRIÇÃO DO DEMANDANTE'] ?? null),
            landStatus: $this->toString($row['STATUS DO TERRENO'] ?? $row['SITUAÇÃO DO TERRENO'] ?? null),
            progress: $this->toString($row['ANDAMENTO'] ?? $row['PROGRESSO'] ?? null),
            inspectionReport: $this->toString($row['RELATÓRIO DE VISTORIA'] ?? $row['RELATORIO DE VISTORIA'] ?? null),
            unitSizeClaim: $this->toString($row['TIPOLOGIA'] ?? $row['PORTE'] ?? null),
            region: $this->toString($row['REGIÃO'] ?? $row['REGIAO'] ?? null),
            requester: $this->toString($row['SOLICITANTE'] ?? $row['DEMANDANTE'] ?? null),
            soilSurveyAndTopography: $this->toString($row['SONDAGEM E TOPOGRAFIA'] ?? $row['LEVANTAMENTO TOPOGRÁFICO'] ?? null),
        );
    }
}
