<?php

declare(strict_types=1);

namespace App\Contract\Application\Trait;

use App\Contract\Application\DTO\ContractExecutionDeadlineOutputDTO;
use App\Contract\Application\DTO\ContractReadjustmentOutputDTO;

/**
 * Normalizes and summarizes statuses used by the general contract extract.
 */
trait ResolvesContractSummaryStatusTrait
{
    private function readjustmentStatus(ContractReadjustmentOutputDTO $record): ?string
    {
        $status = $this->statusLabel($record->status);
        $payment = $this->statusLabel($record->paymentSituation);

        if ($status === null) {
            return $payment;
        }

        if ($status === 'publicado' && in_array($payment, ['pago', 'liquidado'], true)) {
            return 'publicado e liquidado';
        }

        if ($this->isActiveStatus($record->paymentSituation)
            && ! $this->isActiveStatus($record->status)) {
            return $payment;
        }

        return $status;
    }

    private function deadlineStatus(ContractExecutionDeadlineOutputDTO $record): ?string
    {
        return $this->statusLabel(
            $this->isActiveStatus($record->deadlineAddendumStatus)
                ? $record->deadlineAddendumStatus
                : $record->contractSituation,
        );
    }

    /**
     * @param  list<string|null>  $statuses
     */
    private function statusSummary(array $statuses): ?string
    {
        $counts = [];

        foreach ($statuses as $status) {
            $status = $this->statusLabel($status);

            if ($status !== null) {
                $counts[$status] = ($counts[$status] ?? 0) + 1;
            }
        }

        if ($counts === []) {
            return null;
        }

        uksort($counts, function (string $first, string $second): int {
            $priority = [
                'publicado' => 10,
                'publicado e liquidado' => 20,
                'em execução' => 30,
                'em tramitação' => 80,
                'em análise' => 90,
                'pendente' => 100,
            ];

            return ($priority[$first] ?? 50) <=> ($priority[$second] ?? 50);
        });

        if (count($counts) === 1) {
            $status = array_key_first($counts);
            $count = $counts[$status];

            return sprintf(
                '%d %s %s',
                $count,
                $count === 1 ? 'registro' : 'registros',
                $this->pluralizeStatus($status, $count),
            );
        }

        return implode('; ', array_map(
            fn (string $status, int $count): string => sprintf(
                '%d %s',
                $count,
                $this->pluralizeStatus($status, $count),
            ),
            array_keys($counts),
            array_values($counts),
        ));
    }

    private function pluralizeStatus(string $status, int $count): string
    {
        if ($count === 1) {
            return $status;
        }

        return match ($status) {
            'publicado' => 'publicados',
            'publicado e liquidado' => 'publicados e liquidados',
            'pendente' => 'pendentes',
            default => $status,
        };
    }

    private function statusLabel(?string $status): ?string
    {
        $status = $this->nullableValue($status);

        if ($status === null) {
            return null;
        }

        $normalized = $this->withoutAccents(mb_strtoupper($status, 'UTF-8'));

        return match ($normalized) {
            'PUBLICADO' => 'publicado',
            'TRAMITACAO', 'EM TRAMITACAO' => 'em tramitação',
            'ANALISE', 'EM ANALISE' => 'em análise',
            'PENDENTE' => 'pendente',
            'EM EXECUCAO' => 'em execução',
            'PAGO' => 'pago',
            'LIQUIDADO' => 'liquidado',
            default => mb_strtolower($status, 'UTF-8'),
        };
    }

    private function sentenceCase(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return mb_strtoupper(mb_substr($value, 0, 1, 'UTF-8'), 'UTF-8')
            .mb_substr($value, 1, null, 'UTF-8');
    }

    private function isActiveStatus(?string $status): bool
    {
        $normalized = $this->withoutAccents(mb_strtoupper($this->nullableValue($status) ?? '', 'UTF-8'));

        foreach (['TRAMIT', 'PEND', 'ANALIS', 'AGUARD', 'APROVAC', 'INSTRU', 'ELABOR', 'ANDAMENTO', 'NAO PUBLIC'] as $term) {
            if (str_contains($normalized, $term)) {
                return true;
            }
        }

        return false;
    }

    private function withoutAccents(string $value): string
    {
        return strtr($value, [
            'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A',
            'É' => 'E', 'Ê' => 'E',
            'Í' => 'I',
            'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',
            'Ú' => 'U',
            'Ç' => 'C',
        ]);
    }
}
