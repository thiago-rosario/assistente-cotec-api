<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Infra\Repository\EloquentRepository;

use App\Core\TravelReport\Domain\Entity\TravelReportEntity;
use App\Models\TravelReport;

class FindTravelReportBySeiProcessEloquentRepository
{
    public function findBySeiProcess(string $seiProcess): ?TravelReportEntity
    {
        $model = TravelReport::query()
            ->where('sei_process', $seiProcess)
            ->first();

        if ($model === null) {
            return null;
        }

        return TravelReportEntity::fromModel($model);
    }
}
