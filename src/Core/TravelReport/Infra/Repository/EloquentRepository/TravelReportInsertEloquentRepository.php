<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Infra\Repository\EloquentRepository;

use App\Core\TravelReport\Domain\Entity\TravelReportEntity;
use App\Models\TravelReport;

class TravelReportInsertEloquentRepository
{
    public function insert(TravelReportEntity $travelReport): TravelReportEntity
    {
        $model = TravelReport::query()->create($travelReport->toPersistenceArray());

        return TravelReportEntity::fromModel($model);
    }
}
