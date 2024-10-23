<?php

use App\Http\Controllers\Web\InvoiceController;
use App\Http\Controllers\CallbackController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function() {
    return "yadaksadra api.v2";
});

Route::get('invoice', [InvoiceController::class, 'generatePDF']);
Route::get('postal/address', [InvoiceController::class, 'pdfForPost']);

Route::get('callback', [CallbackController::class, 'callback']);
Route::post('callback', [CallbackController::class, 'callback']);
Route::get('verify', [CallbackController::class, 'fastPayment']);
Route::post('verify', [CallbackController::class, 'fastPayment']);
Route::get('wallet/verify', [CallbackController::class, 'wallet']);
Route::post('wallet/verify', [CallbackController::class, 'wallet']);

Route::get('transaction/verify', [CallbackController::class, 'transaction']);
Route::post('transaction/verify', [CallbackController::class, 'transaction']);



//Clear Cache facade value:
Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('optimize');
    Artisan::call('route:cache');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('config:cache');
});

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
