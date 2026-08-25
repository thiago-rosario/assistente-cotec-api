<?php

declare(strict_types=1);

namespace App\Contract\Infra\Message;

use App\Contract\Application\DTO\ContractAdjustmentsOutputDTO;
use App\Contract\Application\DTO\ContractReadjustmentOutputDTO;
use App\Contract\Application\DTO\SearchContractInputDTO;
use App\Contract\Enum\ContractSearchTypeEnum;
use Illuminate\Support\Str;

class ContractAdjustmentReplyBuilder
{
    private const string RecordTitle = 'REAJUSTE E REEQUILÍBRIO';

    private const string RecordSeparator = '────────────';

    /**
     * @var array<string, string>
     */
    private const array Fields = [
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
     * @var list<string>
     */
    private const array MonetaryFields = [
        'contemplatedValue',
    ];

    public function __construct(
        private readonly WhatsappContractRecordValueFormatter $valueFormatter,
    ) {}

    public function build(SearchContractInputDTO $filters, ContractAdjustmentsOutputDTO $result): string
    {
        $recordLabel = $result->total === 1
            ? 'registro de reajuste e reequilíbrio'
            : 'registros de reajustes e reequilíbrios';
        $lines = [sprintf(
            'Encontrei %d %s%s.',
            $result->total,
            $recordLabel,
            $this->searchContext($filters),
        )];
        $index = 0;

        foreach ($result->data as $group) {
            foreach ($group->data as $record) {
                $this->appendRecord($lines, $record, $index, $result->total);
                $index++;
            }
        }

        return trim(implode(PHP_EOL, $lines));
    }

    /**
     * @param  list<string>  $lines
     */
    private function appendRecord(array &$lines, ContractReadjustmentOutputDTO $record, int $index, int $total): void
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
