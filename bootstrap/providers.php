<?php

use App\BuildPanel\Infra\Providers\BuildPanelServiceProvider;
use App\Core\Infra\Providers\CoreServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    BuildPanelServiceProvider::class,
    CoreServiceProvider::class,
];
