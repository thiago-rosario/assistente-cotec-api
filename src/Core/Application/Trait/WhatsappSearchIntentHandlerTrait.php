<?php

declare(strict_types=1);

namespace App\Core\Application\Trait;

trait WhatsappSearchIntentHandlerTrait
{
    /*** @param array<string, mixed> $filters** @return array<string, mixed>*/
    private function executeSearchByIntent(string $intent, array $filters): array
    {
        return match ($intent) {
            'search_technical_notebook' => ($this->searchTechnicalNotebook)($filters),

            'search_construction_demand' => ($this->searchConstructionDemand)($filters),

            'search_land_survey' => ($this->searchLandSurvey)($filters),

            'search_travel_itinerary' => ($this->searchTravelItinerary)($filters),

            default => [
                'total' => 0,
                'data' => [],
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeFilters(mixed $filters): array
    {
        if (! is_array($filters)) {
            return [];
        }

        return array_filter(
            $filters,
            fn (mixed $value): bool => $value !== null && trim((string) $value) !== ''
        );
    }

    private function unknownIntentResponse(): string
    {
        return <<<'TEXT'

Não consegui identificar exatamente qual consulta você deseja fazer.

Você pode me enviar, por exemplo:

Número do processo

Número do contrato

Município

SEI

Nome da unidade

Tipo da demanda

TEXT;
    }
}
