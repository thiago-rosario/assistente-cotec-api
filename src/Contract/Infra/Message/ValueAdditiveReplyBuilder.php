<?php

declare(strict_types=1);

namespace App\Contract\Infra\Message;

use App\Contract\Application\DTO\ContractValueAdditivesOutputDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;
use App\Contract\Application\DTO\ValueAdditiveOutputDTO;
use App\Contract\Enum\ContractSearchTypeEnum;
use Illuminate\Support\Str;

class ValueAdditiveReplyBuilder
{
    private const string RecordTitle = 'ADITIVO DE VALOR';

    private const string RecordSeparator = '────────────';

    /**
     * @var array<string, string>
     */
    private const array Fields = [
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
     * @var list<string>
     */
    private const array MonetaryFields = [
        'value',
        'publishedValue',
    ];

    public function __construct(
        private readonly WhatsappContractRecordValueFormatter $valueFormatter,
    ) {}

    public function build(SearchContractInputDTO $filters, ContractValueAdditivesOutputDTO $result): string
    {
        $recordLabel = $result->total === 1 ? 'registro de aditivo de valor' : 'registros de aditivos de valor';
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
    private function appendRecord(array &$lines, ValueAdditiveOutputDTO $record, int $index, int $total): void
    {
        if ($index > 0) {
            $lines[] = '';
            $lines[] = self::RecordSeparator;
        }

        $lines[] = '';
        $lines[] = sprintf('📌 Registro %d de %d — %s', $index + 1, $total, self::RecordTitle);
        $lines[] = '';

        foreach (self::Fields as $key => $label) {
            $lines[] = sprintf(
                '• %s: %s',
                $label,
                $this->valueFormatter->contractValue(
                    $record,
                    $key,
                    in_array($key, self::MonetaryFields, true),
                ),
            );
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
