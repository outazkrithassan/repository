<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\VolController;
use App\Http\Controllers\VolFrereController;
use App\Http\Controllers\HomeControlle;

use App\Http\Controllers\DepartController;
use App\Http\Controllers\ArriveController;

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
    Route::get('/', [HomeControlle::class, 'Get_saison'])->name('page.dashbord');
    // Route::get('/Count', [HomeControlle::class, 'Count_All'])->name('page.dashbord');
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
    //Export
    Route::get('/export', [ExportController::class, 'index'])->name('export.index');
    // export-data-new
    Route::post('/export/data', [VolController::class, 'Export_Data'])->name('export.data');
    // export-data



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

    // *Les vols

    Route::get('/Vols/arrivee', [ArriveController::class, 'index'])->name('Vols.arrivee');
    Route::get('/vols/data_arrivee', [ArriveController::class, 'datatable'])->name('vols.data_arrivee');
    Route::get('/vols/arrive/create', [ArriveController::class, 'create'])->name('arrive.create');
    Route::post('/vols/arrive/store', [ArriveController::class, "store"])->name('arrive.store');
    Route::get('/vols/arrive/{id}/edit', [ArriveController::class, 'edit'])->name('arrive.edit');
    Route::post('/vols/arrive/{id}/update', [ArriveController::class, 'update'])->name('arrive.update');
    Route::delete('/vols/arrive/{id}/destroy', [ArriveController::class, 'destroy'])->name('arrive.destroy');



    Route::get('/vols/depart', [DepartController::class, 'index'])->name('vols.depart');
    Route::get('/vols/data_depart', [DepartController::class, 'datatable'])->name('vols.data_depart');
    Route::get('/vols/depart/create', [DepartController::class, 'create'])->name('depart.create');
    Route::post('/vols/depart/store', [DepartController::class, "store"])->name('depart.store');
    Route::get('/vols/depart/{id}/edit', [DepartController::class, 'edit'])->name('depart.edit');
    Route::post('/vols/depart/{id}/update', [DepartController::class, 'update'])->name('depart.update');
    Route::delete('/vols/depart/{id}/destroy', [DepartController::class, 'destroy'])->name('depart.destroy');


    // * programme
    Route::get('/programme/saisonnier', [VolController::class, 'saisonnier'])->name('programme.saisonnier');
    Route::get('/vol/data_saisonnier', [VolController::class, 'datatable_saisonnier'])->name('vol.data_saisonnier');
    Route::get('/programme/somaine', [VolController::class, 'somaine'])->name('programme.somaine');
    // Route::get('/fetch_days', [VolController::class, 'getDaysBySeason'])->name('fetch.days');
    Route::get('/vol/data_somaine', [VolController::class, 'datatable_somaine'])->name('vol.data_somaine');


    //exemple de php
    Route::get('/exemple', [VolController::class, 'getFlights']);
});
