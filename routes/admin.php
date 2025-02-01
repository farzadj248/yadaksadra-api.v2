<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\adminController;
use App\Http\Controllers\socialNetworkController;
use App\Http\Controllers\ticketCategoryController;
use App\Http\Controllers\ticketController;
use App\Http\Controllers\articleController;
use App\Http\Controllers\articleCateoryController;
use App\Http\Controllers\articleCommentController;
use App\Http\Controllers\newsController;
use App\Http\Controllers\newsCateoryController;
use App\Http\Controllers\newsCommentController;
use App\Http\Controllers\videoController;
use App\Http\Controllers\videoCommentController;
use App\Http\Controllers\videoCateoryController;
use App\Http\Controllers\productController;
use App\Http\Controllers\productCommentController;
use App\Http\Controllers\productsCategoryController;
use App\Http\Controllers\productCarCompanyController;
use App\Http\Controllers\productCarTypeController;
use App\Http\Controllers\productCarYearController;
use App\Http\Controllers\productCarModelController;
use App\Http\Controllers\ProductCountryBuildersController;
use App\Http\Controllers\productsBrandController;
use App\Http\Controllers\ordersController;
use App\Http\Controllers\orderItemController;
use App\Http\Controllers\cartController;
use App\Http\Controllers\faqsController;
use App\Http\Controllers\faqsCategoryController;
use App\Http\Controllers\shopInfoController;
use App\Http\Controllers\messageController;
use App\Http\Controllers\favoriteController;
use App\Http\Controllers\adminAccessLevelController;
use App\Http\Controllers\adminRolesController;
use App\Http\Controllers\DiscountsController;
use App\Http\Controllers\bannerscontroller;
use App\Http\Controllers\usersMediaController;
use App\Http\Controllers\transactionController;
use App\Http\Controllers\depositRequestsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\fastPaymentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\usersAddressController;
use App\Http\Controllers\CreditRequestsController;
use App\Http\Controllers\NewslettersController;
use App\Http\Controllers\ContactusController;
use App\Http\Controllers\DocumentsController;
use App\Http\Controllers\affiliateHistoryController;
use App\Http\Controllers\MailsController;
use App\Http\Controllers\megamenuController;
use App\Http\Controllers\eventsController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\shippingMethodsController;
use App\Http\Controllers\productsDefaultPropertyController;
use App\Http\Controllers\ViewersStatisticsController;
use App\Http\Controllers\teamController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// https://blog.pusher.com/laravel-jwt/

Route::group(['namespace' => 'admin', 'prefix' => 'admin'], function () {
    Route::post('login', [adminController::class, 'login']);
    Route::post('register', [adminController::class, 'register']);
    Route::post('check_login', [adminController::class, 'checkLogin']);
    Route::post('sendActivationCode', [adminController::class, 'sendActivationCode']);
    Route::post('validateActivationCode', [adminController::class, 'validateActivationCode']);
    Route::post('changePassword', [adminController::class, 'changePassword']);
    Route::post('request_change_password', [adminController::class, 'sendActivationCode']);
});

Route::middleware(['auth:admin','admin'])->group(function () {
    Route::resource('medias', MediaController::class);
    
    Route::post('product/DefinedCar', [productController::class, 'definedCar']);
    Route::post('product/define_cars', [productController::class, 'product_define_cars']);

    Route::resource('productCountryBuilders', ProductCountryBuildersController::class);

    Route::resource('productsCateory', productsCategoryController::class);

    Route::resource('productsBrand', productsBrandController::class);

    Route::resource('productCarType', productCarTypeController::class);

    Route::resource('productCarCompany', productCarCompanyController::class);

    Route::resource('productCarYear', productCarYearController::class);

    Route::resource('productCarModel', productCarModelController::class);

    Route::get('product', [productController::class,'index']);
    Route::post('product', [productController::class,'store']);
    Route::put('product/{id}', [productController::class,'update']);
    Route::get('getProduct/{id}', [productController::class, 'getProductById']);
    Route::delete('product/{id}', [productController::class, 'delete']);

    Route::post('product/stock/update', [ordersController::class, 'updateProductStock']);


    Route::get('orders', [ordersController::class,'index']);
    Route::get('order/{id}', [ordersController::class, 'show']);
    
    Route::post('orders/{id}/edit', [ordersController::class, 'updateOrderProducts']);
    Route::post('orders/removeItem', [ordersController::class, 'removeProductFromOrder']);
    Route::post('order/{id}/cancell', [ordersController::class, 'cancellOrderByAdmin']);

    Route::resource('productsDefaultProperty', productsDefaultPropertyController::class);

    Route::post('orders/status', [ordersController::class, 'changeOrderStatus']);
    Route::post('orders/freightDeliveryReceipt', [ordersController::class, 'setFreightDeliveryReceipt']);
    Route::post('orders/rejection_response', [ordersController::class, 'setReasonRejectionFromAdmin']);
    Route::post('orders/rejection_request', [ordersController::class, 'setOrderRejectionFromUser']);
    Route::post('orders/officialInvoice', [ordersController::class, 'setOfficialInvoice']);
    Route::post('orders/save_deposit_invoice', [ordersController::class, 'saveDepositInvoice']);

    Route::post('orders/{orderCode}/verify', [ordersController::class, 'verifyOrder']);

    Route::resource('team', teamController::class); 

    Route::resource('banner', bannerscontroller::class);

    Route::resource('ticketCategory', ticketCategoryController::class);

    Route::resource('socialNetwork', socialNetworkController::class, [
        'only' => ['store', 'update', 'destroy']
    ]);

    Route::resource('adminAccessLevel', adminAccessLevelController::class);
    Route::resource('adminRoles', adminRolesController::class);

    Route::resource('discounts', DiscountsController::class);

    Route::resource('article', articleController::class, [
        'only' => ['store', 'update', 'destroy']
    ]);

    Route::resource('news', newsController::class, [
        'only' => ['store', 'update', 'destroy']
    ]);

    Route::resource('video', videoController::class, [
        'only' => ['store', 'update', 'destroy']
    ]);

    Route::resource('shippingMethod', shippingMethodsController::class, [
        'only' => ['store', 'update', 'destroy']
    ]);


    Route::post('reports', [ReportsController::class, 'getReportData']);

    Route::post('update/product/excel', [ReportsController::class, 'importProducts']);
    Route::post('software/excel/import', [ReportsController::class, 'updateProductFromSoftware']);

    Route::post('import/products', [ReportsController::class, 'importProducts']);

    Route::post('import/categories', [ReportsController::class, 'importCategories']);
    Route::post('export/categories', [ReportsController::class, 'exportCategories']);

    Route::post('import/brands', [ReportsController::class, 'importBrands']);
    Route::post('export/brands', [ReportsController::class, 'exportBrands']);

    Route::post('import/company', [ReportsController::class, 'importCompany']);
    Route::post('export/company', [ReportsController::class, 'exportCompany']);

    Route::post('import/cars', [ReportsController::class, 'importCars']);
    Route::post('export/cars', [ReportsController::class, 'exportCars']);

    Route::post('import/car_model', [ReportsController::class, 'importCarModels']);
    Route::post('export/car_model', [ReportsController::class, 'exportCarModels']);

    Route::post('import/car_years', [ReportsController::class, 'importCarYears']);
    Route::post('export/car_years', [ReportsController::class, 'exportCarYears']);

    Route::post('import/countryBuilders', [ReportsController::class, 'importCountryBuilders']);
    Route::post('export/countryBuilders', [ReportsController::class, 'exportCountryBuilders']);

    Route::post('user/verify', [AuthController::class, 'verify_account']);
    Route::post('user/change_role', [AuthController::class, 'changeUserRoleFromAdmin']);
    Route::get('user/getAll', [AuthController::class, 'index']);
    Route::get('credits/get', [AuthController::class, 'getUserCredits']);

    Route::get('admin', [adminController::class, 'index']);
    Route::get('admin/{admin}', [adminController::class, 'show']);
    Route::put('admin/{admin}', [adminController::class, 'update']);
    Route::post('admin', [adminController::class, 'store']);
    Route::delete('admin/{admin}', [adminController::class, 'destroy']);

    Route::post('admin/logout', [AuthController::class, 'logout']);

    Route::get('fastPayments', [fastPaymentController::class,'index']);

    Route::get('notifications',[NotificationController::class,'index']);
    Route::post('notification/{id}/seen', [NotificationController::class, 'seenMessage']);
});