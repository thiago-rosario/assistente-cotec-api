<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Infra\Repository\EloquentRepository;

use App\Core\TravelReport\Domain\Entity\TravelReportEntity;
use App\Models\TravelReport;

class FindTravelReportByIdEloquentRepository
{
    public function findById(int $id): ?TravelReportEntity
    {
        $model = TravelReport::query()->find($id);

        if ($model === null) {
            return null;
        }

        return TravelReportEntity::fromModel($model);
    }
}
