<?php

use App\Http\Controllers\GoogleSheetController;
use App\Http\Controllers\SearchConstructionDemandController;
use App\Http\Controllers\SearchGoogleSheetController;
use App\Http\Controllers\SearchLandSurveyController;
use App\Http\Controllers\SearchTechnicalNotebookController;
use App\Http\Controllers\SearchTravelItineraryController;
use App\Http\Controllers\WhatsappMessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/google-sheet', GoogleSheetController::class);
Route::get('/google-sheets/{sheetId}/search', SearchGoogleSheetController::class);
Route::get('/construction-demands/search', SearchConstructionDemandController::class);
Route::get('/land-surveys/search', SearchLandSurveyController::class);
Route::get('/technical-notebooks/search', SearchTechnicalNotebookController::class);
Route::get('/travel-itineraries/search', SearchTravelItineraryController::class);
Route::post('/whatsapp/messages', WhatsappMessageController::class);
