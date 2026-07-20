<?php

use App\Http\Controllers\DeleteTravelReportController;
use App\Http\Controllers\FindTravelReportBySeiProcessController;
use App\Http\Controllers\GoogleSheetController;
use App\Http\Controllers\ListTravelReportByMunicipalityIdController;
use App\Http\Controllers\ListTravelReportsController;
use App\Http\Controllers\PersistTravelReportController;
use App\Http\Controllers\SearchGoogleSheetController;
use App\Http\Controllers\SearchTechnicalNotebookController;
use App\Http\Controllers\WhatsappMessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/google-sheet', GoogleSheetController::class);
Route::get('/google-sheets/{sheetId}/search', SearchGoogleSheetController::class);
Route::get('/technical-notebooks/search', SearchTechnicalNotebookController::class);
Route::post('/whatsapp/messages', WhatsappMessageController::class);
Route::get('/travel-reports', ListTravelReportsController::class);
Route::get('/travel-reports/municipalities/{municipalityId}', ListTravelReportByMunicipalityIdController::class);
Route::get('/travel-reports/sei-process/{seiProcess}', FindTravelReportBySeiProcessController::class);
Route::post('/travel-reports', PersistTravelReportController::class);
Route::delete('/travel-reports/{travelReportId}', DeleteTravelReportController::class);
