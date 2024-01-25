<?php

use App\Http\Controllers\MasterController;
use App\Http\Controllers\ProductController;
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

Route::get('/', function () {
    return redirect()->route('products.index');
});

Route::resource('products', ProductController::class)->except('show');

Route::get('products/import/index', [MasterController::class, 'importProductIndex'])->name('importProductIndex');
Route::post('products/import', [MasterController::class, 'importProduct'])->name('importProduct');
Route::get('products/export', [MasterController::class, 'exportProduct'])->name('exportProduct');
