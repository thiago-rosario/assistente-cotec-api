<?php

use App\Core\BuildPanel\Infra\Providers\BuildPanelServiceProvider;
use App\Core\Conversation\Infra\Providers\CoreServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    BuildPanelServiceProvider::class,
    CoreServiceProvider::class,
];
