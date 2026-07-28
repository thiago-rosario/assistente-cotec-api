<?php

use App\Http\Controllers\GoogleSheetController;
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

Route::post('/whatsapp/validate', function (Request $request) {
    return response()->json([
        'status' => 'success',
        'valid' => true,
        'usuarios' => $request->input('usuarios', []),
    ]);
});
