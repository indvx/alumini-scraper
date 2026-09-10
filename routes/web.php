<?php

use App\Http\Controllers\InstitutionWebController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('/dashboard', [InstitutionWebController::class, 'dashboard'])->name('dashboard');

Route::prefix('institutions')->group(function () {
    Route::get('/', [InstitutionWebController::class, 'search'])->name('public.institutions.index');
    Route::post('/search', [InstitutionWebController::class, 'search'])->name('public.institutions.search');
    Route::get('/export/csv', [InstitutionWebController::class, 'exportCsv'])->name('public.institutions.export');
    Route::get('/{institution}', [InstitutionWebController::class, 'show'])->name('public.institutions.show');
});
