<?php

use Database\Seeders\BahiaMunicipalitiesSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    if (! Schema::hasTable('municipalities')) {
        $migration = require database_path('migrations/TravelReport/2026_07_16_152329_create_municipalities_table.php');

        $migration->up();
    }
});

it('seeds all Bahia municipalities without duplicating records', function (): void {
    $this->seed(BahiaMunicipalitiesSeeder::class);

    expect(DB::table('municipalities')->count())->toBe(417)
        ->and(DB::table('municipalities')
            ->whereIn('name', [
                'Abaíra',
                'Salvador',
                'Luís Eduardo Magalhães',
                'Xique-Xique',
            ])
            ->count())->toBe(4);

    $this->seed(BahiaMunicipalitiesSeeder::class);

    expect(DB::table('municipalities')->count())->toBe(417);
});
