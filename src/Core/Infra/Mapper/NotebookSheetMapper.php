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
            municipality: $this->toString($this->rowValue($row, 'MUNICIPIO', 'MUNICÍPIO')) ?? '',
            relatedProcess: $this->toString($this->rowValue($row, 'PROCESSO RELACIONADO', 'PROCESSO SEI RELACIONADO', 'PROCESSO SEI', 'PROCESSO')),
            unitClaim: $this->toString($this->rowValue($row, 'PLEITO', 'PLEITO UNIDADE', 'PLEITO DA UNIDADE')),
            objectSize: $this->toString($this->rowValue($row, 'TAMANHO DO OBJETO', 'TAMANHO', 'OBJETO')),
            landStatus: $this->toString($this->rowValue($row, 'STATUS DO TERRENO', 'SITUAÇÃO DO TERRENO')),
            requester: $this->toString($this->rowValue($row, 'SOLICITANTE', 'DEMANDANTE')),
            estimatedCost: $this->toFloat($this->rowValue($row, 'CUSTO ESTIMADO', 'VALOR ESTIMADO')),
        );
    }
}
