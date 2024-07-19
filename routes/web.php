<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\VolController;
use App\Http\Controllers\VolFrereController;
use App\Http\Controllers\HomeControlle;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/




Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});



Route::middleware('auth')->group(function () {

    Route::get('/', [HomeControlle::class, 'dashbord'])->name('page.dashbord');
    Route::get('/', [HomeControlle::class, 'Count_All'])->name('page.dashbord');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');


    // * import

    Route::get('/import/data', [ImportController::class, 'datatable'])->name('import.data');
    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::get('/import/create', [ImportController::class, 'create'])->name('import.create');
    Route::get('/import/{id}/edit', [ImportController::class, 'edit'])->name('import.edit');
    Route::post('/import/store', [ImportController::class, 'store'])->name('import.store');
    Route::post('/import/{id}/update', [ImportController::class, 'update'])->name('import.update');
    Route::delete('/import/{id}/destroy', [ImportController::class, 'destroy'])->name('import.destroy');
    // export
    Route::get('vol/export/excel', [VolController::class, 'export'])->name('vol.export.excel');
    Route::get('vol_somaine/export/excel', [VolController::class, 'export_somaine'])->name('vol_somaine.export.excel');
    Route::get('vol_heurs/export/excel', [VolController::class, 'export_heurs'])->name('vol_heurs.export.excel');

    // * vols frere
    Route::get('/vols/frere/data', [VolFrereController::class, 'datatable'])->name('frere.data');
    Route::get('/vols/frere', [VolFrereController::class, "index"])->name('frere.index');
    Route::get('/vols/frere/create', [VolFrereController::class, "create"])->name('frere.create');
    Route::post('/vols/frere/store', [VolFrereController::class, "store"])->name('frere.store');
    Route::get('/vols/frere/{id}/edit', [VolFrereController::class, 'edit'])->name('frere.edit');
    Route::post('/vols/frere/{id}/update', [VolFrereController::class, 'update'])->name('frere.update');
    Route::delete('/vols/frere/{id}/destroy', [VolFrereController::class, 'destroy'])->name('frere.destroy');



    // * programme
    Route::get('/programme/saisonnier', [VolController::class, 'saisonnier'])->name('programme.saisonnier');
    Route::get('/vol/data_saisonnier', [VolController::class, 'datatable_saisonnier'])->name('vol.data_saisonnier');
    Route::get('/programme/somaine', [VolController::class, 'somaine'])->name('programme.somaine');
    Route::get('/vol/data_somaine', [VolController::class, 'datatable_somaine'])->name('vol.data_somaine');



    //exemple de php
    Route::get('/exemple', [VolController::class, 'getFlights']);
});
