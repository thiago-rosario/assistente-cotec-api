<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    Schema::dropIfExists('travel_report_documents');
    Schema::dropIfExists('municipalities');

    $municipalitiesMigration = require database_path('migrations/TravelReport/2026_07_16_152329_create_municipalities_table.php');
    $travelReportDocumentsMigration = require database_path('migrations/TravelReport/2026_07_16_152330_create_travel_report_documents_table.php');

    $municipalitiesMigration->up();
    $travelReportDocumentsMigration->up();
});

it('creates the travel report documents table aligned with the entity', function (): void {
    expect(Schema::hasColumns('travel_report_documents', [
        'id',
        'municipality_id',
        'submitted_by_user_id',
        'file_name',
        'file_path',
        'file_size',
        'sei_process',
        'mime_type',
        'created_at',
        'updated_at',
        'deleted_at',
    ]))->toBeTrue();
});
