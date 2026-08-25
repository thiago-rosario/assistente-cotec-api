<?php

use App\BuildPanel\Infra\Providers\BuildPanelServiceProvider;
use App\Contract\Infra\Providers\ContractServiceProvider;
use App\Core\Infra\Provider\CoreServiceProvider;

return [
    CoreServiceProvider::class,
    BuildPanelServiceProvider::class,
    ContractServiceProvider::class,
];
