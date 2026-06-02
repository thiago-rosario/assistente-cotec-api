<?php

declare(strict_types=1);

namespace App\Core\Infra\Mapper;

use App\Core\Application\Interfaces\NotebookSheetMapperInterface;
use App\Core\Domain\Entity\NotebookEntity;
use App\Core\Infra\Trait\CastsSpreadsheetValues;

class NotebookSheetMapper implements NotebookSheetMapperInterface
{
    use CastsSpreadsheetValues;

    public function fromRow(array $row): NotebookEntity
    {
        return new NotebookEntity(
            municipality: $this->toString($row['MUNICIPIO'] ?? $row['MUNICÍPIO'] ?? '') ?? '',
            relatedProcess: $this->toString($row['PROCESSO RELACIONADO'] ?? $row['PROCESSO'] ?? null),
            unitClaim: $this->toString($row['PLEITO'] ?? $row['PLEITO UNIDADE'] ?? null),
            objectSize: $this->toString($row['TAMANHO DO OBJETO'] ?? $row['TAMANHO'] ?? $row['OBJETO'] ?? null),
            landStatus: $this->toString($row['STATUS DO TERRENO'] ?? $row['SITUAÇÃO DO TERRENO'] ?? null),
            requester: $this->toString($row['SOLICITANTE'] ?? $row['DEMANDANTE'] ?? null),
            estimatedCost: $this->toFloat($row['CUSTO ESTIMADO'] ?? $row['VALOR ESTIMADO'] ?? null),
        );
    }
}
