<?php

use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\LocationSearchController;
use App\Http\Controllers\RFPsPlatformController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('/dashboard', [InstitutionController::class, 'dashboard'])->name('dashboard');

Route::prefix('institutions')->group(function () {
    Route::get('/', [InstitutionController::class, 'index'])->name('institutions.index');
    Route::post('/search', [LocationSearchController::class, 'search'])->name('institutions.search');
    Route::get('/export/csv', [InstitutionController::class, 'exportCsv'])->name('institutions.export');
    Route::get('/{institution}', [InstitutionController::class, 'show'])->name('institutions.show');
});

Route::prefix('rfps-platform')->group(function () {
    Route::get('/', [RFPsPlatformController::class, 'index'])->name('rfps-platform.index');
    Route::get('/ai-search', [RFPsPlatformController::class, 'aiSearch'])->name('rfps-platform.ai-search');
    Route::get('/create', [RFPsPlatformController::class, 'create'])->name('rfps-platform.create');
    Route::post('/', [RFPsPlatformController::class, 'store'])->name('rfps-platform.store');
    Route::get('/export/csv', [RFPsPlatformController::class, 'exportCsv'])->name('rfps-platform.export');
    Route::get('/{rfpsPlatform}', [RFPsPlatformController::class, 'show'])->name('rfps-platform.show');
    Route::get('/{rfpsPlatform}/edit', [RFPsPlatformController::class, 'edit'])->name('rfps-platform.edit');
    Route::put('/{rfpsPlatform}', [RFPsPlatformController::class, 'update'])->name('rfps-platform.update');
    Route::delete('/{rfpsPlatform}', [RFPsPlatformController::class, 'destroy'])->name('rfps-platform.destroy');
});
