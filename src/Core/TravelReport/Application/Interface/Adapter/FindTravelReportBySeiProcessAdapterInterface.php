<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Application\Interface\Adapter;

use App\Core\TravelReport\Application\DTO\FindTravelReportBySeiProcessInputDTO;
use App\Core\TravelReport\Application\DTO\FindTravelReportBySeiProcessOutputDTO;

interface FindTravelReportBySeiProcessAdapterInterface
{
    /**
     * @param  array{sei_process: string}  $payload
     */
    public function toInput(array $payload): FindTravelReportBySeiProcessInputDTO;

    /**
     * @return array<string, mixed>
     */
    public function toArray(FindTravelReportBySeiProcessOutputDTO $dto): array;
}
