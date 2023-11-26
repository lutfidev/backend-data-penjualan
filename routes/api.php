<?php

use App\Http\Controllers\TransaksiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('/transaksi-list', [TransaksiController::class, 'index']);
Route::post('/transaksi-add', [TransaksiController::class, 'store']);
Route::post('/transaksi-edit', [TransaksiController::class, 'edit']);
Route::post('/transaksi-delete', [TransaksiController::class, 'delete']);
Route::post('/transaksi-search-and-sort', [TransaksiController::class, 'searchAndSort']);
Route::post('/transaksi-compare-sales', [TransaksiController::class, 'compareSales']);
