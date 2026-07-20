<?php

declare(strict_types=1);

namespace App\Core\TravelReport\Infra\Repository\EloquentRepository;

use App\Models\TravelReport;

class DeleteTravelReportEloquentRepository
{
    public function delete(int $id): bool
    {
        $model = TravelReport::query()->find($id);

        return $model !== null && $model->delete() === true;
    }
}
