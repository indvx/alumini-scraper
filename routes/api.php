<?php

use App\Http\Controllers\Api\InstitutionController;
use Illuminate\Support\Facades\Route;

Route::prefix('institutions')->group(function () {
    Route::post('/search', [InstitutionController::class, 'search']);
    Route::get('/', [InstitutionController::class, 'index']);
    Route::get('/export/csv', [InstitutionController::class, 'exportCsv']);
    Route::get('/searches', [InstitutionController::class, 'searches']);
    Route::get('/{institution}', [InstitutionController::class, 'show']);
});

Route::prefix('schools')->group(function () {
    Route::post('/search', [InstitutionController::class, 'search']);
});
