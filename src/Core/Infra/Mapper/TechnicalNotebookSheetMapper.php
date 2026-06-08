<?php

declare(strict_types=1);

namespace App\Core\Infra\Mapper;

use App\Core\Application\Interfaces\Mapper\TechnicalNotebookSheetMapperInterface;
use App\Core\Domain\Entity\TechnicalNotebookEntity;
use App\Core\Infra\Trait\CastsSpreadsheetValues;

class TechnicalNotebookSheetMapper implements TechnicalNotebookSheetMapperInterface
{
    use CastsSpreadsheetValues;

    public function fromRow(array $row): TechnicalNotebookEntity
    {
        return new TechnicalNotebookEntity(
            item: $this->toInt($row['ITEM'] ?? null),
            stage: $this->toString($row['ETAPA'] ?? null),
            municipality: $this->toString($this->rowValue($row, 'MUNICIPIO', 'MUNICÍPIO')) ?? '',
            process: $this->toString($this->rowValue($row, 'PROCESSO', 'PROCESSO SEI')),
            force: $this->toString($row['FORÇA'] ?? null),
            claim: $this->toString($row['PLEITO'] ?? null),
            typology: $this->toString($row['TIPOLOGIA'] ?? null),
            typologyObservation: $this->toString($row['OBS. TIPOLOGIA'] ?? null),
            estimatedValue: $this->toFloat($row['VALOR ESTIMADO'] ?? null),
            inspection: $this->toString($row['VISTORIA'] ?? null),
            seiReport: $this->toString($row['RELATÓRIO SEI'] ?? null),
            landStatus: $this->toString($row['STATUS DO TERRENO'] ?? null),
            landRegularization: $this->toString($row['REGULARIZAÇÃO FUNDIÁRIA'] ?? null),
            soilStudy: $this->toString($row['ESTUDO DE SOLO'] ?? null),
            environmental: $this->toString($row['AMBIENTAL'] ?? null),
            inspectionComment: $this->toString($row['COMENTARIO DA FISCALIZAÇÃO'] ?? null),
            claimStage: $this->toString($row['ETAPA PLEITO'] ?? null),
            biddingSei: $this->toString($row['SEI LICITAÇÃO'] ?? null),
            contract: $this->toString($row['CONTRATO'] ?? null),
            fiplanInstrument: $this->toString($row['INSTRUMENTO FIPLAN'] ?? null),
            buildStatus: $this->toString($row['STATUS DE OBRA'] ?? null),
            inaugurationDate: null,
        );
    }
}
