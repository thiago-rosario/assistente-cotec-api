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
            municipality: $this->toString($this->rowValue($row, 'MUNICIPIO', 'MUNICÍPIO')) ?? '',
            force: $this->toString($this->rowValue($row, 'FORÇA')),
            process: $this->toString($this->rowValue($row, 'PROCESSO', 'PROCESSO SEI')),
            unitClaim: $this->toString($this->rowValue($row, 'PLEITO', 'PLEITO UNIDADE', 'PLEITO DA UNIDADADE')),
            requesterDescription: $this->toString($this->rowValue($row, 'DESCRIÇÃO DO SOLICITANTE', 'DESCRIÇÃO DO DEMANDANTE')),
            landStatus: $this->toString($this->rowValue($row, 'STATUS DO TERRENO', 'SITUAÇÃO DO TERRENO')),
            progress: $this->toString($this->rowValue($row, 'ANDAMENTO', 'PROGRESSO')),
            inspectionReport: $this->toString($this->rowValue($row, 'RELATÓRIO DE VISTORIA', 'RELATORIO DE VISTORIA', 'RELATÓRIO VISTORIA')),
            unitSizeClaim: $this->toString($this->rowValue($row, 'TIPOLOGIA', 'PORTE', 'PLEITO UNIDADE TAMANHO')),
            region: $this->toString($this->rowValue($row, 'REGIÃO', 'REGIAO', 'REGIÃO (RISP 2023)')),
            requester: $this->toString($this->rowValue($row, 'SOLICITANTE', 'DEMANDANTE')),
            soilSurveyAndTopography: $this->toString($this->rowValue($row, 'SONDAGEM E TOPOGRAFIA', 'LEVANTAMENTO TOPOGRÁFICO')),
        );
    }
}
