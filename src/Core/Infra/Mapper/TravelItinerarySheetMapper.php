<?php

declare(strict_types=1);

namespace App\Core\Infra\Mapper;

use App\Core\Application\Interfaces\Mapper\TravelItinerarySheetMapperInterface;
use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Infra\Trait\CastsSpreadsheetValues;

class TravelItinerarySheetMapper implements TravelItinerarySheetMapperInterface
{
    use CastsSpreadsheetValues;

    public function fromRow(array $row): TravelItineraryEntity
    {
        return new TravelItineraryEntity(
            municipality: $this->toString($this->rowValue($row, 'MUNICIPIO', 'MUNICÍPIO')) ?? '',
            process: $this->toString($this->rowValue($row, 'PROCESSO', 'PROCESSO SEI')),
            region: $this->toString($this->rowValue($row, 'REGIÃO', 'REGIAO', 'REGIÃO (RISP 2023)')),
            unitClaim: $this->toString($this->rowValue($row, 'PLEITO', 'PLEITO UNIDADE')),
            force: $this->toString($this->rowValue($row, 'FORÇA')),
            requester: $this->toString($this->rowValue($row, 'SOLICITANTE', 'DEMANDANTE', 'REQUISITANTE')),
            landStatus: $this->toString($this->rowValue($row, 'STATUS DO TERRENO', 'SITUAÇÃO DO TERRENO')),
            progress: $this->toString($this->rowValue($row, 'ANDAMENTO', 'PROGRESSO')),
            focalPointContact: $this->toString($this->rowValue($row, 'CONTATO PONTO FOCAL', 'PONTO FOCAL', 'CONTATO - PONTO FOCAL')),
            route: $this->toString($this->rowValue($row, 'ROTA')),
            mapLink: $this->toString($this->rowValue($row, 'LINK MAPA', 'MAPA')),
        );
    }
}
