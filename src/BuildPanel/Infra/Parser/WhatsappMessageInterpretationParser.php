<?php

declare(strict_types=1);

namespace App\BuildPanel\Infra\Parser;

use App\BuildPanel\Application\DTO\WhatsappMessageInterpretationDTO;
use App\BuildPanel\Application\Interfaces\Parser\WhatsappMessageInterpretationParserInterface;
use JsonException;

class WhatsappMessageInterpretationParser implements WhatsappMessageInterpretationParserInterface
{
    /**
     * @param  array<string, mixed>|string  $interpretation
     *
     * @throws JsonException
     */
    public function parse(array|string $interpretation): WhatsappMessageInterpretationDTO
    {
        $payload = is_array($interpretation)
            ? $interpretation
            : $this->decodeJson($interpretation);

        return new WhatsappMessageInterpretationDTO(
            intent: $this->normalizeIntent((string) ($payload['intent'] ?? 'unknown')),
            filters: $this->normalizeFilters($payload['filters'] ?? []),
        );
    }

    /**
     * @return array<string, mixed>
     *
     * @throws JsonException
     */
    private function decodeJson(string $interpretation): array
    {
        $decodedInterpretation = json_decode($interpretation, true, 512, JSON_THROW_ON_ERROR);

        return is_array($decodedInterpretation) ? $decodedInterpretation : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeFilters(mixed $filters): array
    {
        if (! is_array($filters)) {
            return [];
        }

        $normalizedFilters = [];

        foreach ($filters as $key => $value) {
            if (! is_string($key) || is_array($value)) {
                continue;
            }

            $value = trim((string) $value);

            if ($value === '') {
                continue;
            }

            $normalizedFilters[$this->normalizeFilterKey($key)] = $value;
        }

        return $normalizedFilters;
    }

    private function normalizeFilterKey(string $key): string
    {
        $key = mb_strtolower(str_replace('-', '_', trim($key)));

        return match ($key) {
            'build_status' => 'buildStatus',
            'land_status' => 'landStatus',
            'municipality', 'municipio' => 'municipality',
            'forca', 'force_name' => 'force',
            'processo' => 'process',
            'regiao' => 'region',
            'situacao_terreno' => 'landStatus',
            'situacao_construcao' => 'buildStatus',
            'solicitante' => 'requester',
            'busca', 'consulta', 'contrato', 'sei', 'termo', 'query' => 'term',
            default => $key,
        };
    }

    private function normalizeIntent(string $intent): string
    {
        $intent = mb_strtolower(str_replace('-', '_', trim($intent)));

        return match ($intent) {
            'search_technical_notebook', 'technical_notebook', 'caderno_tecnico', 'cadernos_tecnicos' => 'search_technical_notebook',
            default => 'unknown',
        };
    }
}
