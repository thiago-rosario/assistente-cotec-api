<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Infra\Repository\EloquentRepository;

use App\Core\TravelReport\Domain\Entity\TravelReportEntity;
use App\Models\TravelReport;

class FindAllTravelReportsEloquentRepository
{
    /**
     * @return list<TravelReportEntity>
     */
    public function all(): array
    {
        return TravelReport::query()
            ->get()
            ->map(fn (TravelReport $model): TravelReportEntity => TravelReportEntity::fromModel($model))
            ->all();
    }
}
