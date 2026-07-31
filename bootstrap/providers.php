<?php

use App\BuildPanel\Infra\Providers\BuildPanelServiceProvider;
use App\Core\Infra\Provider\CoreServiceProvider;

return [
    CoreServiceProvider::class,
    BuildPanelServiceProvider::class,
];
