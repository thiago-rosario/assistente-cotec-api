<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\Interface\Adapter;

use App\Core\TravelReport\Application\DTO\ListTravelReportsInputDTO;
use App\Core\TravelReport\Application\DTO\ListTravelReportsOutputDTO;

interface ListTravelReportsAdapterInterface
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function toInput(array $payload): ListTravelReportsInputDTO;

    /**
     * @return array<string, mixed>
     */
    public function toArray(ListTravelReportsOutputDTO $dto): array;
}
