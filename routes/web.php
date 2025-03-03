<?php

use App\Events\RealTimeMessageEvent;
use App\Http\Controllers\Web\InvoiceController;
use App\Http\Controllers\CallbackController;
use App\Models\Notification;
use Carbon\Carbon;
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
   $notification = Notification::create([
        'action' => 'event',
        'message' => [
            'action' => 'order',
            'id' => 558,
            'userName' => "farzad jafari",
            'date' => Carbon::now()->format('Y-m-d H:i:s'),
            'message' => 'سفارش جدید دریافت شد.'
        ]
    ]);
    // event(new RealTimeMessageEvent('notification','hello world'));
    event(new RealTimeMessageEvent('event', $notification));
    Artisan::call('cache:clear');
    Artisan::call('optimize');
    Artisan::call('route:cache');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('config:cache');
    return 'cleared';
});

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
