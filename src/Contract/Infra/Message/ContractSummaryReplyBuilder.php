<?php

declare(strict_types=1);

namespace App\Contract\Infra\Message;

use App\Contract\Application\DTO\ContractSummaryOutputDTO;
use App\Contract\Application\DTO\FindContractSummaryOutputDTO;

class ContractSummaryReplyBuilder
{
    private const string RecordSeparator = '────────────';

    /**
     * @var array<string, string>
     */
    private const array ValueAdditiveFields = [
        'entryDate' => 'Data de entrada no protocolo',
        'stage' => 'Etapa',
        'contractNumber' => 'Número do contrato',
        'company' => 'Empresa',
        'municipality' => 'Município',
        'unit' => 'Unidade',
        'seiProcess' => 'Processo SEI',
        'type' => 'Tipo',
        'value' => 'Valor',
        'status' => 'Status',
        'currentLocation' => 'Local atual',
        'processingTimeDays' => 'Tempo de tramitação',
        'situation' => 'Situação',
        'publicationDate' => 'Data da publicação',
        'publishedValue' => 'Valor após publicação',
        'publicationTimeDays' => 'Tempo de publicação',
        'additiveNumber' => 'Número do aditivo',
        'observation' => 'Observações',
    ];

    /**
     * @var array<string, string>
     */
    private const array ContractAdjustmentFields = [
        'entryDate' => 'Data de ingresso',
        'company' => 'Empresa',
        'ceirfEntryDate' => 'Entrada na CEIRF',
        'ceirfLastMovementDate' => 'Última movimentação na CEIRF',
        'contractNumber' => 'Número do contrato',
        'seiProcess' => 'Processo SEI',
        'apostilleNumber' => 'Número da apostila',
        'contemplatedValue' => 'Valor contemplado',
        'contemplatedIncidencePeriod' => 'Período de incidência contemplado',
        'status' => 'Status',
        'location' => 'Local',
        'processingTimeDays' => 'Tempo de tramitação',
        'publicationDate' => 'Data da publicação',
        'publicationTimeDays' => 'Tempo de publicação',
        'observation' => 'Observações',
        'paymentSituation' => 'Situação do pagamento',
        'paymentSei' => 'SEI de pagamento',
    ];

    /**
     * @var array<string, string>
     */
    private const array ExecutionDeadlineFields = [
        'entryDate' => 'Data de entrada',
        'company' => 'Empresa',
        'contractNumber' => 'Contrato',
        'validityEndDate' => 'Final da vigência',
        'municipality' => 'Município',
        'executionEndDate' => 'Final da execução',
        'remainingExecutionDays' => 'Dias para vencer a execução',
        'remainingValidityDays' => 'Dias para vencer a vigência',
        'contractSituation' => 'Situação do contrato',
        'seiProcess' => 'Processo SEI',
        'location' => 'Local',
        'deadlineAddendumStatus' => 'Status do aditivo de prazo',
        'processingTimeDays' => 'Tempo de tramitação',
        'publicationDate' => 'Data da publicação',
        'publicationTimeDays' => 'Tempo de publicação',
        'observation' => 'Observações',
    ];

    /**
     * @var list<string>
     */
    private const array ValueAdditiveMonetaryFields = [
        'value',
        'publishedValue',
    ];

    /**
     * @var list<string>
     */
    private const array ContractAdjustmentMonetaryFields = [
        'contemplatedValue',
    ];

    public function __construct(
        private readonly WhatsappContractRecordValueFormatter $valueFormatter,
    ) {}

    public function build(FindContractSummaryOutputDTO $result): string
    {
        if ($result->data === []) {
            return 'Nenhum registro informado.';
        }

        $lines = [];

        foreach ($result->data as $index => $summary) {
            if ($index > 0) {
                $lines[] = '';
                $lines[] = self::RecordSeparator;
                $lines[] = '';
            }

            $this->appendSummary($lines, $summary);
        }

        return trim(implode(PHP_EOL, $lines));
    }

    /**
     * @param  list<string>  $lines
     */
    private function appendSummary(array &$lines, ContractSummaryOutputDTO $summary): void
    {
        $lines[] = sprintf('📋 RESUMO DO CONTRATO %s', $this->valueFormatter->value($summary->contractNumber));
        $lines[] = '';
        $this->appendSummaryFields($lines, $summary);

        $lines[] = '';
        $lines[] = '💰 ADITIVOS DE VALOR';
        $lines[] = '';
        $this->appendRecordSection(
            $lines,
            $summary->valueAdditives,
            self::ValueAdditiveFields,
            self::ValueAdditiveMonetaryFields,
            'ADITIVO DE VALOR',
        );

        $lines[] = '';
        $lines[] = '📊 REAJUSTES E REEQUILÍBRIOS';
        $lines[] = '';
        $this->appendRecordSection(
            $lines,
            $summary->readjustments,
            self::ContractAdjustmentFields,
            self::ContractAdjustmentMonetaryFields,
            'REAJUSTE E REEQUILÍBRIO',
        );

        $lines[] = '';
        $lines[] = '📅 PRAZOS DE EXECUÇÃO';
        $lines[] = '';
        $this->appendRecordSection(
            $lines,
            $summary->executionDeadlines,
            self::ExecutionDeadlineFields,
            [],
            'CONTROLE DE PRAZO',
        );
    }

    /**
     * @param  list<string>  $lines
     */
    private function appendSummaryFields(array &$lines, ContractSummaryOutputDTO $summary): void
    {
        $municipalities = $summary->municipalities === []
            ? $this->valueFormatter->value($summary->municipality)
            : implode(', ', $summary->municipalities);

        $lines[] = sprintf('• Empresa: %s', $this->valueFormatter->value($summary->company));
        $lines[] = sprintf('• Município: %s', $municipalities);
        $lines[] = sprintf('• Processo SEI: %s', $this->valueFormatter->value($summary->seiProcess));
        $lines[] = sprintf('• Objeto: %s', $this->valueFormatter->value($summary->object));
        $lines[] = sprintf('• Valor inicial: %s', $this->valueFormatter->value($summary->initialValue, true));
        $lines[] = sprintf('• Valor atualizado: %s', $this->valueFormatter->value($summary->updatedValue, true));
        $lines[] = sprintf('• Início da vigência: %s', $this->valueFormatter->value($summary->validityStartDate));
        $lines[] = sprintf('• Final da vigência: %s', $this->valueFormatter->value($summary->validityEndDate));
        $lines[] = sprintf('• Prazo de execução: %s', $this->valueFormatter->value($summary->executionDeadline));
        $lines[] = sprintf('• Situação atual: %s', $this->valueFormatter->value($summary->currentSituation));
        $this->appendListField($lines, 'Processos relacionados', $summary->processes);
        $this->appendListField($lines, 'Status', $summary->statuses);
        $this->appendListField($lines, 'Observações', $summary->observations);
    }

    /**
     * @param  list<string>  $lines
     * @param  list<string>  $values
     */
    private function appendListField(array &$lines, string $label, array $values): void
    {
        if ($values === []) {
            $lines[] = sprintf('• %s: Não informado', $label);

            return;
        }

        foreach ($values as $value) {
            $lines[] = sprintf('• %s: %s', $label, $this->valueFormatter->value($value));
        }
    }

    /**
     * @param  list<string>  $lines
     * @param  list<object>  $records
     * @param  array<string, string>  $fields
     * @param  list<string>  $monetaryFields
     */
    private function appendRecordSection(
        array &$lines,
        array $records,
        array $fields,
        array $monetaryFields,
        string $recordTitle,
    ): void {
        if ($records === []) {
            $lines[] = 'Nenhum registro informado.';

            return;
        }

        $total = count($records);

        foreach ($records as $index => $record) {
            if ($index > 0) {
                $lines[] = '';
                $lines[] = self::RecordSeparator;
            }

            $lines[] = sprintf('📌 Registro %d de %d — %s', $index + 1, $total, $recordTitle);
            $lines[] = '';

            foreach ($fields as $key => $label) {
                $lines[] = sprintf(
                    '• %s: %s',
                    $label,
                    $this->valueFormatter->contractValue(
                        $record,
                        $key,
                        in_array($key, $monetaryFields, true),
                    ),
                );
            }
        }
    }
}
