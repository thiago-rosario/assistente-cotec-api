<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Infra\Repository\EloquentRepository;

use App\Core\TravelReport\Domain\Entity\TravelReportEntity;
use App\Models\TravelReport;

class FindTravelReportByMunicipalityIdEloquentRepository
{
    /**
     * @return list<TravelReportEntity>
     */
    public function findByMunicipalityId(int $municipalityId): array
    {
        return TravelReport::query()
            ->where('municipality_id', $municipalityId)
            ->get()
            ->map(fn (TravelReport $model): TravelReportEntity => TravelReportEntity::fromModel($model))
            ->all();
    }
}
