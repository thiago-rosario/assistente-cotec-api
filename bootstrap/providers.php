<?php

use App\BuildPanel\Infra\Providers\BuildPanelServiceProvider;
use App\Core\Infra\Provider\CoreServiceProvider;
use App\TechnicalInspectionReport\Infra\Providers\TechnicalInspectionReportServiceProvider;

return [
    CoreServiceProvider::class,
    BuildPanelServiceProvider::class,
    TechnicalInspectionReportServiceProvider::class,
];
