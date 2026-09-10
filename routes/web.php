<?php

use App\Http\Controllers\InstitutionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('/dashboard', [InstitutionController::class, 'dashboard'])->name('dashboard');

Route::prefix('institutions')->group(function () {
    Route::get('/', [InstitutionController::class, 'search'])->name('institutions.index');
    Route::post('/search', [InstitutionController::class, 'search'])->name('institutions.search');
    Route::get('/export/csv', [InstitutionController::class, 'exportCsv'])->name('institutions.export');
    Route::get('/{institution}', [InstitutionController::class, 'show'])->name('institutions.show');
});

Route::prefix('rfps')->group(function () {
    Route::get('/', [RFPWebController::class, 'search'])->name('rfps.index');
    Route::post('/search', [RFPWebController::class, 'search'])->name('rfps.search');
    Route::get('/export/csv', [RFPWebController::class, 'exportCsv'])->name('rfps.export');
    Route::get('/{rfp}', [RFPWebController::class, 'show'])->name('rfps.show');
});
