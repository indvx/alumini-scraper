<?php

use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\InstitutionRFPPlatformController;
use App\Http\Controllers\LocationLookupController;
use App\Http\Controllers\LocationSearchController;
use App\Http\Controllers\RFPsPlatformController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('/dashboard', [InstitutionController::class, 'dashboard'])->name('dashboard');

Route::prefix('locations')->group(function () {
    Route::get('/countries', [LocationLookupController::class, 'getCountries'])->name('locations.countries');
    Route::get('/states', [LocationLookupController::class, 'getStates'])->name('locations.states');
    Route::get('/cities', [LocationLookupController::class, 'getCities'])->name('locations.cities');
});

Route::prefix('institutions')->group(function () {
    Route::get('/', [InstitutionController::class, 'index'])->name('institutions.index');
    Route::get('/create', [InstitutionController::class, 'create'])->name('institutions.create');
    Route::post('/', [InstitutionController::class, 'store'])->name('institutions.store');
    Route::post('/search', [LocationSearchController::class, 'search'])->name('institutions.search');
    Route::get('/export/csv', [InstitutionController::class, 'exportCsv'])->name('institutions.export');
    Route::get('/{institution}', [InstitutionController::class, 'show'])->name('institutions.show');
    Route::get('/{institution}/edit', [InstitutionController::class, 'edit'])->name('institutions.edit');
    Route::put('/{institution}', [InstitutionController::class, 'update'])->name('institutions.update');
    Route::delete('/{institution}', [InstitutionController::class, 'destroy'])->name('institutions.destroy');
    Route::post('/{institution}/rfp-platforms', [InstitutionRFPPlatformController::class, 'storeInstitutionPlatform'])->name('institutions.rfp-platforms.store');
    Route::delete('/{institution}/rfp-platforms/{rfpsPlatform}', [InstitutionRFPPlatformController::class, 'destroyInstitutionPlatform'])->name('institutions.rfp-platforms.destroy');
});

Route::prefix('rfps-platform')->group(function () {
    Route::get('/', [RFPsPlatformController::class, 'index'])->name('rfps-platform.index');
    Route::get('/create', [RFPsPlatformController::class, 'create'])->name('rfps-platform.create');
    Route::post('/', [RFPsPlatformController::class, 'store'])->name('rfps-platform.store');
    Route::get('/export/csv', [RFPsPlatformController::class, 'exportCsv'])->name('rfps-platform.export');
    Route::get('/{rfpsPlatform}', [RFPsPlatformController::class, 'show'])->name('rfps-platform.show');
    Route::get('/{rfpsPlatform}/edit', [RFPsPlatformController::class, 'edit'])->name('rfps-platform.edit');
    Route::put('/{rfpsPlatform}', [RFPsPlatformController::class, 'update'])->name('rfps-platform.update');
    Route::delete('/{rfpsPlatform}', [RFPsPlatformController::class, 'destroy'])->name('rfps-platform.destroy');
    Route::post('/{rfpsPlatform}/institutions', [InstitutionRFPPlatformController::class, 'storePlatformInstitution'])->name('rfps-platform.institutions.store');
    Route::delete('/{rfpsPlatform}/institutions/{institution}', [InstitutionRFPPlatformController::class, 'destroyPlatformInstitution'])->name('rfps-platform.institutions.destroy');
});
