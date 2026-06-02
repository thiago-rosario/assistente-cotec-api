<?php

declare(strict_types=1);

namespace App\Core\Infra\Service;

use App\Core\Application\Interfaces\WhatsappMessageResponseFormatterInterface;

class WhatsappMessageResponseFormatter implements WhatsappMessageResponseFormatterInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @param  array{term: string|null, total: int, data: list<array<string, mixed>>}  $result
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function format(string $intent, array $filters, array $result): array
    {
        $reply = $result['total'] === 0
            ? 'Não encontrei registros para essa consulta. Tente informar o número do processo, município, força, região ou situação.'
            : $this->buildFoundRecordsReply($intent, $result);

        return [
            'reply' => $reply,
            'intent' => $intent,
            'total' => $result['total'],
            'data' => $result['data'],
            'filters' => $filters,
        ];
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function unknownIntent(): array
    {
        return [
            'reply' => 'Não consegui identificar exatamente qual consulta você deseja fazer. Envie, por exemplo, o número do processo, município, força, região ou situação.',
            'intent' => 'unknown',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ];
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function error(): array
    {
        return [
            'reply' => 'Não consegui processar sua solicitação agora. Tente novamente informando o número do processo, município, força, região ou situação.',
            'intent' => 'error',
            'total' => 0,
            'data' => [],
            'filters' => [],
        ];
    }

    /**
     * @param  array{term: string|null, total: int, data: list<array<string, mixed>>}  $result
     */
    private function buildFoundRecordsReply(string $intent, array $result): string
    {
        $label = $this->intentLabel($intent);
        $records = array_slice($result['data'], 0, 3);
        $lines = [
            sprintf('Encontrei %d registro(s) em %s.', $result['total'], $label),
        ];

        foreach ($records as $index => $record) {
            $lines[] = sprintf('%d. %s', $index + 1, $this->summarizeRecord($record));
        }

        if ($result['total'] > count($records)) {
            $lines[] = 'Mostrei os primeiros resultados. Refine a busca para localizar um registro específico.';
        }

        return implode(PHP_EOL, $lines);
    }

    private function intentLabel(string $intent): string
    {
        return match ($intent) {
            'search_technical_notebook' => 'cadernos técnicos',
            'search_construction_demand' => 'demandas de construção',
            'search_land_survey' => 'levantamentos de terreno',
            'search_travel_itinerary' => 'itinerários de viagem',
            default => 'registros',
        };
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function summarizeRecord(array $record): string
    {
        $parts = array_filter([
            $this->recordValue($record, 'process') !== null ? 'Processo: '.$this->recordValue($record, 'process') : null,
            $this->recordValue($record, 'municipality') !== null ? 'Município: '.$this->recordValue($record, 'municipality') : null,
            $this->recordValue($record, 'force') !== null ? 'Força: '.$this->recordValue($record, 'force') : null,
            $this->recordValue($record, 'region') !== null ? 'Região: '.$this->recordValue($record, 'region') : null,
            $this->recordValue($record, 'landStatus') !== null ? 'Terreno: '.$this->recordValue($record, 'landStatus') : null,
            $this->recordValue($record, 'progress') !== null ? 'Andamento: '.$this->recordValue($record, 'progress') : null,
            $this->recordValue($record, 'buildStatus') !== null ? 'Construção: '.$this->recordValue($record, 'buildStatus') : null,
            $this->recordValue($record, 'requester') !== null ? 'Solicitante: '.$this->recordValue($record, 'requester') : null,
        ]);

        return $parts === [] ? 'Registro sem resumo disponível.' : implode(' | ', $parts);
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function recordValue(array $record, string $key): ?string
    {
        if (! isset($record[$key])) {
            return null;
        }

        $value = trim((string) $record[$key]);

        return $value === '' ? null : $value;
    }
}
