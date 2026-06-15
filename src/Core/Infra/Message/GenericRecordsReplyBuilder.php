<?php

declare(strict_types=1);

namespace App\Core\Infra\Message;

class GenericRecordsReplyBuilder
{
    private const int RecordsLimit = 3;

    /**
     * @var array<string, string>
     */
    private const array SummaryFields = [
        'process' => 'Processo',
        'municipality' => 'Município',
        'force' => 'Força',
        'region' => 'Região',
        'landStatus' => 'Terreno',
        'progress' => 'Andamento',
        'buildStatus' => 'Construção',
        'requester' => 'Solicitante',
    ];

    public function __construct(
        private readonly WhatsappIntentLabel $intentLabel,
        private readonly WhatsappRecordValueFormatter $valueFormatter,
    ) {}

    /**
     * @param  array{term: string|null, total: int, data: list<array<string, mixed>>}  $result
     */
    public function build(string $intent, array $result): string
    {
        $records = $this->limitedRecords($result['data']);
        $lines = [
            sprintf('Encontrei %d registro(s) em %s.', $result['total'], $this->intentLabel->for($intent)),
        ];

        foreach ($records as $index => $record) {
            $lines[] = sprintf('%d. %s', $index + 1, $this->summarizeRecord($record));
        }

        $this->appendRefinementHint($lines, $result['total'], count($records));

        return implode(PHP_EOL, $lines);
    }

    /**
     * @param  list<array<string, mixed>>  $records
     * @return list<array<string, mixed>>
     */
    private function limitedRecords(array $records): array
    {
        return array_slice($records, 0, self::RecordsLimit);
    }

    /**
     * @param  list<string>  $lines
     */
    private function appendRefinementHint(array &$lines, int $total, int $shown): void
    {
        if ($total <= $shown) {
            return;
        }

        $lines[] = '';
        $lines[] = 'Mostrei os primeiros resultados. Refine a busca para localizar um registro específico.';
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function summarizeRecord(array $record): string
    {
        $parts = [];

        foreach (self::SummaryFields as $key => $label) {
            $value = $this->valueFormatter->recordValue($record, $key);

            if ($value !== null) {
                $parts[] = $label.': '.$value;
            }
        }

        return $parts === [] ? 'Registro sem resumo disponível.' : implode(' | ', $parts);
    }
}
