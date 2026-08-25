<?php

declare(strict_types=1);

namespace App\Contract\Infra\Message;

use App\Contract\Application\DTO\ContractExecutionDeadlineOutputDTO;
use App\Contract\Application\DTO\ContractExecutionDeadlinesOutputDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;
use App\Contract\Enum\ContractSearchTypeEnum;
use Illuminate\Support\Str;

class ExecutionDeadlineReplyBuilder
{
    private const string RecordTitle = 'CONTROLE DE PRAZO';

    private const string RecordSeparator = '────────────';

    /**
     * @var array<string, string>
     */
    private const array Fields = [
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

    public function __construct(
        private readonly WhatsappContractRecordValueFormatter $valueFormatter,
    ) {}

    public function build(SearchContractInputDTO $filters, ContractExecutionDeadlinesOutputDTO $result): string
    {
        $recordLabel = $result->total === 1 ? 'registro de controle de prazo' : 'registros de controle de prazo';
        $lines = [sprintf(
            'Encontrei %d %s%s.',
            $result->total,
            $recordLabel,
            $this->searchContext($filters),
        )];

        foreach ($result->data as $index => $record) {
            $this->appendRecord($lines, $record, $index, $result->total);
        }

        return trim(implode(PHP_EOL, $lines));
    }

    /**
     * @param  list<string>  $lines
     */
    private function appendRecord(array &$lines, ContractExecutionDeadlineOutputDTO $record, int $index, int $total): void
    {
        if ($index > 0) {
            $lines[] = '';
            $lines[] = self::RecordSeparator;
        }

        $lines[] = '';
        $lines[] = sprintf('📌 Registro %d de %d — %s', $index + 1, $total, self::RecordTitle);
        $lines[] = '';

        foreach (self::Fields as $key => $label) {
            $lines[] = sprintf('• %s: %s', $label, $this->valueFormatter->contractValue($record, $key));
        }
    }

    private function searchContext(SearchContractInputDTO $filters): string
    {
        return match ($filters->searchType) {
            ContractSearchTypeEnum::Municipality => ' para o município '.Str::upper($filters->searchTerm),
            ContractSearchTypeEnum::ContractNumber => ' para o contrato '.$filters->searchTerm,
            ContractSearchTypeEnum::Company,
            ContractSearchTypeEnum::SeiProcess => '',
        };
    }
}
