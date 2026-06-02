<?php

use App\Http\Controllers\GoogleSheetController;
use App\Http\Controllers\SearchGoogleSheetController;
use App\Http\Controllers\SearchTechnicalNotebookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/google-sheet', GoogleSheetController::class);
Route::get('/google-sheets/{sheetId}/search', SearchGoogleSheetController::class);
Route::get('/technical-notebooks/search', SearchTechnicalNotebookController::class);
