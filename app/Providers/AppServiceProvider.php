<?php

namespace App\Providers;

use App\Core\Application\Interfaces\PythonBridgeEventParserInterface;
use App\Core\Application\Interfaces\PythonMessageOutputParserInterface;
use App\Core\Application\Interfaces\PythonMessagePayloadAdapterInterface;
use App\Core\Application\Interfaces\PythonMessagePayloadMapperInterface;
use App\Core\Infra\Adapter\PythonMessagePayloadAdapter;
use App\Core\Infra\Mapper\PythonMessagePayloadMapper;
use App\Core\Infra\Parser\PythonBridgeEventParser;
use App\Core\Infra\Parser\PythonMessageOutputParser;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PythonMessagePayloadAdapterInterface::class, PythonMessagePayloadAdapter::class);
        $this->app->bind(PythonBridgeEventParserInterface::class, PythonBridgeEventParser::class);
        $this->app->bind(PythonMessagePayloadMapperInterface::class, PythonMessagePayloadMapper::class);
        $this->app->bind(PythonMessageOutputParserInterface::class, PythonMessageOutputParser::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
