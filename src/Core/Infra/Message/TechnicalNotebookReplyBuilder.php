<?php

declare(strict_types=1);

namespace App\Core\Infra\Message;

use Illuminate\Support\Str;

class TechnicalNotebookReplyBuilder
{
    /**
     * @var array<string, string>
     */
    private const array Fields = [
        'item' => 'Item',
        'stage' => 'Etapa',
        'municipality' => 'Município',
        'process' => 'Processo',
        'force' => 'Força',
        'claim' => 'Pleito',
        'typology' => 'Tipologia',
        'typologyObservation' => 'Obs. tipologia',
        'estimatedValue' => 'Valor estimado',
        'inspection' => 'Vistoria',
        'seiReport' => 'Relatório SEI',
        'landStatus' => 'Status do terreno',
        'landRegularization' => 'Regularização fundiária',
        'soilStudy' => 'Estudo de solo',
        'environmental' => 'Ambiental',
        'inspectionComment' => 'Comentário da fiscalização',
        'claimStage' => 'Etapa pleito',
        'biddingSei' => 'SEI licitação',
        'contract' => 'Contrato',
        'fiplanInstrument' => 'Instrumento FIPLAN',
        'buildStatus' => 'Status de obra',
        'inaugurationDate' => 'Data de inauguração',
    ];

    public function __construct(
        private readonly WhatsappRecordValueFormatter $valueFormatter,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @param  array{term: string|null, total: int, data: list<array<string, mixed>>}  $result
     */
    public function build(array $filters, array $result): string
    {
        $municipality = filled($filters['municipality'] ?? null)
            ? (string) $filters['municipality']
            : $this->valueFormatter->recordValue($result['data'][0] ?? [], 'municipality');

        $lines = [sprintf(
            'Encontrei %d %s%s.',
            $result['total'],
            $result['total'] === 1 ? 'registro' : 'registros',
            $municipality !== null ? ' para o município '.Str::upper($municipality) : ' em cadernos técnicos',
        )];

        foreach ($result['data'] as $index => $record) {
            $lines[] = '';
            $lines[] = sprintf('%d. Registro do Caderno Técnico', $index + 1);

            foreach (self::Fields as $key => $label) {
                $lines[] = sprintf('   %s: %s', $label, $this->valueFormatter->technicalNotebookValue($record, $key));
            }
        }

        return implode(PHP_EOL, $lines);
    }
}
