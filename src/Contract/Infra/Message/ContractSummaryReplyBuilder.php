<?php

declare(strict_types=1);

namespace App\Contract\Infra\Message;

use App\Contract\Application\DTO\ContractExtractDTO;
use App\Contract\Application\DTO\FindContractSummaryOutputDTO;

class ContractSummaryReplyBuilder
{
    private const string RecordSeparator = '────────────';

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

            $this->appendExtract($lines, $summary);
        }

        return trim(implode(PHP_EOL, $lines));
    }

    /**
     * @param  list<string>  $lines
     */
    private function appendExtract(array &$lines, ContractExtractDTO $summary): void
    {
        $lines[] = sprintf('📋 EXTRATO CONTRATUAL — %s', $this->valueFormatter->value($summary->contractNumber));
        $lines[] = '';

        $this->appendOptionalField($lines, '🏢 Empresa', $summary->company);
        $this->appendOptionalField($lines, '📍 Município', $summary->municipality);
        $this->appendOptionalField($lines, '📄 Processo principal', $summary->seiProcess);
        $this->appendOptionalField($lines, '📌 Situação atual', $summary->currentSituation);
        $this->appendOptionalField($lines, '💰 Valor atualizado', $summary->updatedValue, true);

        $lines[] = sprintf(
            '➕ Aditivos: %s',
            $summary->additivesStatus
                ?? $this->recordCount($summary->additivesCount),
        );
        $this->appendReadjustments($lines, $summary);
        $lines[] = sprintf(
            '📅 Prazos de execução: %s',
            $summary->executionDeadlinesStatus ?? 'Sem registros',
        );

        $this->appendOptionalField($lines, '🔄 Última movimentação', $summary->lastMovementDate);
        $this->appendOptionalField($lines, '⚠️ Pendência atual', $summary->currentPending);
    }

    /**
     * @param  list<string>  $lines
     */
    private function appendReadjustments(array &$lines, ContractExtractDTO $summary): void
    {
        if ($summary->readjustmentsStatus === null) {
            $lines[] = sprintf(
                '📊 Reajustes e reequilíbrios: %s',
                $this->recordCount($summary->readjustmentsCount),
            );

            return;
        }

        if (! str_contains($summary->readjustmentsStatus, '; ')) {
            $lines[] = sprintf(
                '📊 Reajustes e reequilíbrios: %s',
                $summary->readjustmentsStatus,
            );

            return;
        }

        $lines[] = sprintf(
            '📊 Reajustes e reequilíbrios: %d registros',
            $summary->readjustmentsCount,
        );

        foreach (explode('; ', $summary->readjustmentsStatus) as $status) {
            $lines[] = '  • '.$status;
        }
    }

    /**
     * @param  list<string>  $lines
     */
    private function appendOptionalField(
        array &$lines,
        string $label,
        mixed $value,
        bool $monetary = false,
    ): void {
        if ($value === null || (is_string($value) && $this->isEmptyValue($value))) {
            return;
        }

        $lines[] = sprintf(
            '%s: %s',
            $label,
            $this->valueFormatter->value($value, $monetary),
        );
    }

    private function recordCount(int $count): string
    {
        return $count === 0
            ? 'Sem registros'
            : sprintf('%d %s', $count, $count === 1 ? 'registro' : 'registros');
    }

    private function isEmptyValue(string $value): bool
    {
        return in_array(trim($value), ['', '-', '/'], true);
    }
}
