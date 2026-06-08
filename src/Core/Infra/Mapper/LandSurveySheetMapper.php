<?php

declare(strict_types=1);

namespace App\Core\Infra\Mapper;

use App\Core\Application\Interfaces\Mapper\LandSurveySheetMapperInterface;
use App\Core\Domain\Entity\LandSurveyEntity;
use App\Core\Infra\Trait\CastsSpreadsheetValues;

class LandSurveySheetMapper implements LandSurveySheetMapperInterface
{
    use CastsSpreadsheetValues;

    public function fromRow(array $row): LandSurveyEntity
    {
        return new LandSurveyEntity(
            municipality: $this->toString($this->rowValue($row, 'MUNICIPIO', 'MUNICÍPIO')) ?? '',
            process: $this->toString($this->rowValue($row, 'PROCESSO', 'PROCESSO SEI')),
            region: $this->toString($this->rowValue($row, 'REGIÃO', 'REGIAO', 'REGIÃO (RISP 2023)')),
            unitSizeClaim: $this->toString($this->rowValue($row, 'PLEITO', 'TIPOLOGIA', 'PLEITO UNIDADE TAMANHO')),
            force: $this->toString($this->rowValue($row, 'FORÇA')),
            requester: $this->toString($this->rowValue($row, 'SOLICITANTE', 'DEMANDANTE', 'REQUISITANTE')),
            ownership: $this->toString($this->rowValue($row, 'TITULARIDADE', 'PROPRIEDADE')),
            topography: $this->toString($this->rowValue($row, 'TOPOGRAFIA', 'LEVANTAMENTO TOPOGRÁFICO')),
            landStatus: $this->toString($this->rowValue($row, 'STATUS DO TERRENO', 'SITUAÇÃO DO TERRENO')),
            progress: $this->toString($this->rowValue($row, 'ANDAMENTO', 'PROGRESSO')),
            municipalityFocalPointContact: $this->toString($this->rowValue($row, 'CONTATO MUNICÍPIO', 'PONTO FOCAL MUNICÍPIO', 'CONTATO - PONTO FOCAL MUNICÍPIO')),
            militaryPoliceFocalPointContact: $this->toString($this->rowValue($row, 'CONTATO PM', 'PONTO FOCAL PM', 'PONTO FOCAL POLÍCIA MILITAR')),
            civilPoliceFocalPointContact: $this->toString($this->rowValue($row, 'CONTATO PC', 'PONTO FOCAL PC', 'PONTO FOCAL POLÍCIA CIVIL')),
            documentationLink: $this->toString($this->rowValue($row, 'LINK DOCUMENTAÇÃO', 'DOCUMENTAÇÃO', 'LINK PARA DOCUMENTAÇÃO')),
            updatedAt: $this->toDateTimeImmutable($this->rowValue($row, 'ATUALIZADO EM', 'DATA DE ATUALIZAÇÃO')),
            observations: $this->toString($this->rowValue($row, 'OBSERVAÇÕES', 'OBSERVACOES')),
            requestedAt: $this->toDateTimeImmutable($this->rowValue($row, 'SOLICITADO EM', 'DATA DE SOLICITAÇÃO', 'DATA DA SOLICITAÇÃO')),
        );
    }
}
