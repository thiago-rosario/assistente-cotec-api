<?php

declare(strict_types=1);

namespace App\Core\Infra\Message;

class WhatsappIntentLabel
{
    public function for(string $intent): string
    {
        return match ($intent) {
            'search_technical_notebook' => 'Painel de Obras',
            'search_construction_demand' => 'demandas de construção',
            'search_land_survey' => 'levantamentos de terreno',
            'search_travel_itinerary' => 'itinerários de viagem',
            default => 'registros',
        };
    }
}
