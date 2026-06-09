<?php

declare(strict_types=1);

namespace App\Core\Infra\Service;

use App\Core\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use DateTimeInterface;
use Illuminate\Support\Str;

class WhatsappMessageResponseFormatter implements WhatsappMessageResponseFormatterInterface
{
    private const int RecordsLimit = 3;

    /**
     * @param  array<string, mixed>  $filters
     * @param  array{term: string|null, total: int, data: list<array<string, mixed>>}  $result
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function format(string $intent, array $filters, array $result): array
    {
        $reply = $result['total'] === 0
            ? 'Não encontrei registros para essa consulta. Tente informar o número do processo, município, força, região ou situação.'
            : $this->buildFoundRecordsReply($intent, $filters, $result);

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
    public function greeting(): array
    {
        return $this->emptyResponse(
            intent: 'greeting',
            reply: 'Oi! Posso ajudar com consultas por número do processo, município, força, região ou situação.',
        );
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function unknownIntent(): array
    {
        return $this->emptyResponse(
            intent: 'unknown',
            reply: 'Não consegui identificar exatamente qual consulta você deseja fazer. Envie, por exemplo, o número do processo, município, força, região ou situação.',
        );
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function unsupportedMessageContent(): array
    {
        return $this->emptyResponse(
            intent: 'unsupported_message_content',
            reply: 'Recebi sua mensagem, mas não consegui ler conteúdo em texto. Envie a consulta em texto com o número do processo, município, força, região ou situação.',
        );
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function rateLimited(): array
    {
        return $this->emptyResponse(
            intent: 'rate_limited',
            reply: 'Recebi sua mensagem, mas o serviço de interpretação está temporariamente no limite. Tente novamente em alguns instantes.',
        );
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function dataSourceUnavailable(): array
    {
        return $this->emptyResponse(
            intent: 'data_source_unavailable',
            reply: 'Entendi sua consulta, mas não consegui acessar a fonte de dados agora. Tente novamente em alguns instantes.',
        );
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function error(): array
    {
        return $this->emptyResponse(
            intent: 'error',
            reply: 'Não consegui processar sua solicitação agora. Tente novamente informando o número do processo, município, força, região ou situação.',
        );
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    private function emptyResponse(string $intent, string $reply): array
    {
        return [
            'reply' => $reply,
            'intent' => $intent,
            'total' => 0,
            'data' => [],
            'filters' => [],
        ];
    }

    /**
     * @param  array{term: string|null, total: int, data: list<array<string, mixed>>}  $result
     */
    private function buildFoundRecordsReply(string $intent, array $filters, array $result): string
    {
        if ($intent === 'search_technical_notebook') {
            return $this->buildTechnicalNotebookReply($filters, $result);
        }

        $label = $this->intentLabel($intent);
        $records = $this->limitedRecords($result['data']);
        $lines = [
            sprintf('Encontrei %d registro(s) em %s.', $result['total'], $label),
        ];

        foreach ($records as $index => $record) {
            $lines[] = sprintf('%d. %s', $index + 1, $this->summarizeRecord($record));
        }

        $this->appendRefinementHint($lines, $result['total'], count($records));

        return implode(PHP_EOL, $lines);
    }

    /**
     * @param  array{term: string|null, total: int, data: list<array<string, mixed>>}  $result
     */
    private function buildTechnicalNotebookReply(array $filters, array $result): string
    {
        $municipality = filled($filters['municipality'] ?? null)
            ? (string) $filters['municipality']
            : $this->recordValue($result['data'][0] ?? [], 'municipality');

        $lines = [sprintf(
            'Encontrei %d %s%s.',
            $result['total'],
            $result['total'] === 1 ? 'registro' : 'registros',
            $municipality !== null ? ' para o município '.Str::upper($municipality) : ' em cadernos técnicos',
        )];

        foreach ($result['data'] as $index => $record) {
            $lines[] = '';
            $lines[] = sprintf('%d. Registro do Caderno Técnico', $index + 1);

            foreach ($this->technicalNotebookFields() as $key => $label) {
                $lines[] = sprintf('   %s: %s', $label, $this->formatTechnicalNotebookValue($record, $key));
            }
        }

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
    private function estimatedValue(array $record): string
    {
        $value = $record['estimatedValue'] ?? null;

        if (! is_numeric($value)) {
            return 'Não informado';
        }

        return 'R$ '.number_format((float) $value, 2, ',', '.');
    }

    /**
     * @return array<string, string>
     */
    private function technicalNotebookFields(): array
    {
        return [
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
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function formatTechnicalNotebookValue(array $record, string $key): string
    {
        if ($key === 'estimatedValue') {
            return $this->estimatedValue($record);
        }

        $value = $record[$key] ?? null;

        if ($value instanceof DateTimeInterface) {
            return $value->format('d/m/Y');
        }

        $value = trim((string) $value);

        return $value === '' ? 'Não informado' : $value;
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
