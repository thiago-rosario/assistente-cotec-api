<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Infra\Repository\EloquentRepository;

use App\Core\TravelReport\Domain\Entity\TravelReportEntity;
use App\Models\TravelReport;

class FindTravelReportBySubmittedByUserIdEloquentRepository
{
    /**
     * @return list<TravelReportEntity>
     */
    public function findBySubmittedByUserId(string $submittedByUserId): array
    {
        return TravelReport::query()
            ->where('submitted_by_user_id', $submittedByUserId)
            ->get()
            ->map(fn (TravelReport $model): TravelReportEntity => TravelReportEntity::fromModel($model))
            ->all();
    }
}
