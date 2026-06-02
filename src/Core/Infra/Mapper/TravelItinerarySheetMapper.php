<?php

declare(strict_types=1);

namespace App\Core\Infra\Mapper;

use App\Core\Application\Interfaces\TravelItinerarySheetMapperInterface;
use App\Core\Domain\Entity\TravelItineraryEntity;
use App\Core\Infra\Trait\CastsSpreadsheetValues;

class TravelItinerarySheetMapper implements TravelItinerarySheetMapperInterface
{
    use CastsSpreadsheetValues;

    public function fromRow(array $row): TravelItineraryEntity
    {
        return new TravelItineraryEntity(
            municipality: $this->toString($row['MUNICIPIO'] ?? $row['MUNICÍPIO'] ?? '') ?? '',
            process: $this->toString($row['PROCESSO'] ?? null),
            region: $this->toString($row['REGIÃO'] ?? $row['REGIAO'] ?? null),
            unitClaim: $this->toString($row['PLEITO'] ?? $row['PLEITO UNIDADE'] ?? null),
            force: $this->toString($row['FORÇA'] ?? null),
            requester: $this->toString($row['SOLICITANTE'] ?? $row['DEMANDANTE'] ?? null),
            landStatus: $this->toString($row['STATUS DO TERRENO'] ?? $row['SITUAÇÃO DO TERRENO'] ?? null),
            progress: $this->toString($row['ANDAMENTO'] ?? $row['PROGRESSO'] ?? null),
            focalPointContact: $this->toString($row['CONTATO PONTO FOCAL'] ?? $row['PONTO FOCAL'] ?? null),
            route: $this->toString($row['ROTA'] ?? null),
            mapLink: $this->toString($row['LINK MAPA'] ?? $row['MAPA'] ?? null),
        );
    }
}
