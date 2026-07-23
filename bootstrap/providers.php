<?php

use App\Core\BuildPanel\Infra\Providers\BuildPanelServiceProvider;
use App\Core\Conversation\Infra\Providers\CoreServiceProvider;
use App\Core\Identity\Infra\Providers\IdentityServiceProvider;
use App\Core\TravelReport\Infra\Providers\TravelReportServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    BuildPanelServiceProvider::class,
    CoreServiceProvider::class,
    TravelReportServiceProvider::class,
    IdentityServiceProvider::class,
];
