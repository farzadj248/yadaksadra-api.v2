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
use App\Http\Controllers\MediaController;
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
use App\Http\Controllers\shippingMethodsController;
use App\Http\Controllers\productsDefaultPropertyController;
use App\Http\Controllers\ViewersStatisticsController;
use App\Http\Controllers\teamController;
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
Route::group(['namespace' => 'auth', 'prefix' => 'auth'], function() {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'authenticate']);
    Route::post('check_login', [AuthController::class, 'checkLogin']);
    Route::post('sendActivationCode', [AuthController::class, 'sendActivationCode']);
    Route::post('validateActivationCode', [AuthController::class, 'validateActivationCode']);
    Route::post('changePassword', [AuthController::class, 'changePassword']);
    Route::post('request_change_password', [AuthController::class, 'sendActivationCode']);

    Route::post('admin/register', [adminController::class, 'register']);
    Route::post('admin/login', [adminController::class, 'authenticate']);
    Route::post('admin/sendActivationCode', [adminController::class, 'sendActivationCode']);
    Route::post('admin/validateActivationCode', [adminController::class, 'validateActivationCode']);
    Route::post('admin/changePassword', [adminController::class, 'changePassword']);
    Route::post('admin/updatePasswd', [adminController::class, 'updatePasswd']);
});

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::group(['namespace' => 'auth', 'prefix' => 'auth'], function() {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class,'getAuthenticatedUser']);
        Route::post('user/verify', [AuthController::class, 'verify_account']);
        Route::post('user/change_role', [AuthController::class, 'changeUserRoleFromAdmin']);
        Route::get('user/getAll', [AuthController::class,'index']);
        Route::get('credits/get', [AuthController::class,'getUserCredits']);

        Route::get('admin', [adminController::class, 'index']);
        Route::get('admin/{admin}', [adminController::class, 'show']);
        Route::put('admin/{admin}', [adminController::class, 'update']);
        Route::post('admin', [adminController::class, 'store']);
        Route::delete('admin/{admin}', [adminController::class, 'destroy']);
        
        Route::post('admin/logout', [AuthController::class, 'logout']);
    });

    Route::resource('cart', cartController::class);
    Route::get('cart_products', [cartController::class, 'getProducts']);
    Route::get('cart_shipping', [cartController::class, 'shipping']);
    Route::get('cart_payment', [cartController::class, 'payment']);
    Route::post('set_delivery', [cartController::class, 'set_delivery_type']);
    Route::post('set_delivery_time', [cartController::class, 'set_order_delivery_time']);
    
    Route::post('majorShopping/buy', [cartController::class,'majorShopping']);
    
    
    Route::post('request_official_invoice', [cartController::class, 'request_official_invoice']);
    
    Route::post('add_coupon', [cartController::class, 'add_coupon']);
    Route::post('remove_coupon', [cartController::class, 'remove_coupon']);
    
    Route::resource('message', messageController::class);
    Route::post('message/seen', [messageController::class,'seen_message']);
    Route::post('send_message', [messageController::class, 'user_send']);
    Route::get('unreadMessages/get', [messageController::class, 'unreadMessages']);
    
    Route::post('payment', [PaymentController::class, 'payment']);
    Route::post('payment/wallet', [PaymentController::class, 'chargeWallet']);
    
    Route::post('user/wallet/balance', [AuthController::class,'getUserWalletBalance']);
    Route::post('user/get', [AuthController::class,'getUser']);
    Route::get('user/{user}', [AuthController::class,'show']);
    Route::put('user/update', [AuthController::class,'update_user']);
    Route::put('user/address', [usersAddressController::class,'updateAddress']);
    Route::post('user/address', [usersAddressController::class,'getAddress']);
    Route::post('user/profile/marketing', [AuthController::class,'getUserProfileMarketing']);
    Route::get('organization/users', [AuthController::class,'getOrganizationUsers']);
    Route::post('user/checkout', [AuthController::class,'checkoutRequest']);
    
    //user-request for change role
    Route::put('user/update/profile', [AuthController::class,'storeInitialProfileInfo']);
    Route::put('user/update/role', [AuthController::class,'changeAccountRole']);
    Route::put('user/role/request', [AuthController::class,'saveFinalRequestChangeRole']);
    Route::get('user/role/status', [AuthController::class,'getRoleChangingStatus']);
    
    Route::post('user/summery', [AuthController::class,'getUserProfileSummery']);
    Route::post('user/orders', [AuthController::class,'getUserOrders']);
    Route::post('user/getOrders', [ordersController::class,'getOrders']);
    Route::get('user/order/{order}', [ordersController::class, 'getUserOrder']);
    
    Route::post('user/transactions', [AuthController::class,'getUserTransactions']);
    Route::get('user/transactions/get', [transactionController::class,'getUserTransactions']);
    Route::get('user/unPayed/transactions', [transactionController::class,'unPayedTransactions']);
    Route::get('user/fast_payments/get', [fastPaymentController::class,'getUserTransactions']);
    Route::post('user/deposit_requests', [depositRequestsController::class,'getUserDepositRequests']);
    Route::post('user/deposit_requests/get', [depositRequestsController::class,'userDepositRequests']);
    Route::post('user/withdraw', [depositRequestsController::class,'withdraw_wallet']);
    Route::post('payment/transaction', [PaymentController::class,'paymentTransaction']);
    
    Route::post('user/documents', [AuthController::class,'getUserDocuments']);
    Route::post('user/marketing', [AuthController::class,'getUserMarketing']);
    Route::post('user/discount_codes', [AuthController::class,'getUserDiscountCodes']);
    Route::post('user/comments', [AuthController::class,'getUserComments']);
    Route::post('user/messages', [messageController::class, 'getUserMessages']);
    Route::post('user/article/comments', [articleCommentController::class,'getUserComments']);
    Route::post('user/product/comments', [productCommentController::class,'getUserComments']);
    Route::post('user/ticket', [ticketController::class,'getUserTicket']);
    Route::post('user/credit_requests/get', [CreditRequestsController::class,'getUserCreditRequests']);
    
    Route::post('user/coupons', [DiscountsController::class,'getUserDiscounts']);
    
    //admin
    Route::resource('productComment', productCommentController::class);
    Route::post('productComment/add_score', [productCommentController::class,'changeScoreComment']);
    Route::post('documents', [DocumentsController::class,'index']);
    Route::put('documents', [DocumentsController::class,'update']);
    Route::post('documents/activate', [DocumentsController::class,'activate']);
    Route::post('documents/requestCredit', [DocumentsController::class,'requestCredit']);

    Route::post('user/updateDocument', [DocumentsController::class,'updateDocument']);
    Route::post('user/request_credit_again', [DocumentsController::class,'request_credit_again']);
    
    Route::resource('users_medias', usersMediaController::class);
    
    Route::resource('discounts', DiscountsController::class);
    
    Route::resource('article', articleController::class, [
        'only' => ['store', 'update','destroy']
    ]);
    
    Route::resource('news', newsController::class, [
        'only' => ['store', 'update','destroy']
    ]);
    
    Route::resource('video', videoController::class, [
        'only' => ['store', 'update','destroy']
    ]);
    
    Route::resource('shippingMethod', shippingMethodsController::class, [
        'only' => ['store', 'update','destroy']
    ]);
    
    
    Route::post('reports', [ReportsController::class,'getReportData']);
    
    Route::post('update/product/excel', [ReportsController::class,'importProducts']);
    Route::post('software/excel/import', [ReportsController::class,'updateProductFromSoftware']);
    
    Route::post('import/products', [ReportsController::class,'importProducts']);
    
    Route::post('import/categories', [ReportsController::class,'importCategories']);
    Route::post('export/categories', [ReportsController::class,'exportCategories']);
    
    Route::post('import/brands', [ReportsController::class,'importBrands']);
    Route::post('export/brands', [ReportsController::class,'exportBrands']);
    
    Route::post('import/company', [ReportsController::class,'importCompany']);
    Route::post('export/company', [ReportsController::class,'exportCompany']);
    
    Route::post('import/cars', [ReportsController::class,'importCars']);
    Route::post('export/cars', [ReportsController::class,'exportCars']);
    
    Route::post('import/car_model', [ReportsController::class,'importCarModels']);
    Route::post('export/car_model', [ReportsController::class,'exportCarModels']);
    
    Route::post('import/car_years', [ReportsController::class,'importCarYears']);
    Route::post('export/car_years', [ReportsController::class,'exportCarYears']);
    
    Route::post('import/countryBuilders', [ReportsController::class,'importCountryBuilders']);
    Route::post('export/countryBuilders', [ReportsController::class,'exportCountryBuilders']);
    
    Route::get('events/logs/get', [eventsController::class, 'get']);
    Route::get('events/logs/getAll', [eventsController::class, 'getAll']);
    Route::delete('events/logs/delete', [eventsController::class, 'remove']);
    
    Route::put('banners/horizontalBannerHeader/update',[bannerscontroller::class, 'updateHorizontalBannerHeader']);
    Route::put('banners/horizontalBannerSlider/update',[bannerscontroller::class, 'updateHorizontalBannerSlider']);
    Route::put('banners/horizontalBanner/update',[bannerscontroller::class, 'updateHorizontalBanner']);
    Route::put('banners/horizontalVideo/update',[bannerscontroller::class, 'updateHorizontalVideo']);
    
    Route::put('shopInfos/termsAndConditions',[shopInfoController::class, 'termsAndConditions']);
    Route::put('shopInfos/aboutus',[shopInfoController::class, 'about']);
    
    Route::resource('adminAccessLevel', adminAccessLevelController::class);
    Route::resource('adminRoles', adminRolesController::class);
    
    Route::post('admin/home', [HomeController::class, 'adminHome']);
    
    Route::post('mail', [MailsController::class,'send']);
    Route::post('mail_bulk', [MailsController::class,'send_bulk']);
    
    Route::resource('ticket', ticketController::class);
    Route::resource('ticketCategory', ticketCategoryController::class);
    
    Route::resource('socialNetwork', socialNetworkController::class, [
        'only' => ['store', 'update','destroy']
    ]);
    
    Route::post('product/DefinedCar', [productController::class,'definedCar']);
    Route::post('product/define_cars', [productController::class,'product_define_cars']);
    
    Route::resource('productCountryBuilders', ProductCountryBuildersController::class, [
        'only' => ['store', 'update','destroy']
    ]);
    
    Route::resource('productsCateory', productsCategoryController::class, [
        'only' => ['store', 'update','destroy']
    ]);
    
    Route::resource('productsBrand', productsBrandController::class, [
        'only' => ['store', 'update','destroy']
    ]);
    
    Route::resource('productCarType', productCarTypeController::class, [
        'only' => ['store', 'update','destroy']
    ]);
    
    Route::resource('productCarCompany', productCarCompanyController::class, [
        'only' => ['store', 'update','destroy']
    ]);
    
    Route::resource('productCarYear', productCarYearController::class, [
        'only' => ['store', 'update','destroy']
    ]);
    
    Route::resource('productCarModel', productCarModelController::class, [
        'only' => ['store', 'update','destroy']
    ]);
    
    Route::resource('product', productController::class, [
        'only' => ['store', 'update','destroy']
    ]);
    
    Route::post('product/stock/update',[ordersController::class, 'updateProductStock']);
    

    Route::put('orders/{id}/edit',[ordersController::class, 'updateOrderProducts']);
    Route::post('orders/{id}/removeItem',[ordersController::class, 'removeProductFromOrder']);
    Route::post('order/{id}/cancell',[ordersController::class, 'cancellOrderByAdmin']);
    
    Route::post('message/verifyOrder',[ordersController::class, 'verifyOrder']);
    
    Route::post('Viewers/statistics',[ViewersStatisticsController::class, 'getAll']);
});


Route::get('sale_statistics', [HomeController::class, 'saleStatistics']);
Route::get('admin/unread_received_data', [HomeController::class, 'unread_received_data']);

Route::resource('fastPayments', fastPaymentController::class);
Route::resource('depositRequest', depositRequestsController::class);

Route::resource('medias', MediaController::class);

Route::resource('megamenu', megamenuController::class);
Route::get('mega_menu/get',[megamenuController::class, 'getMegaMenu']);
Route::put('mega_menu/update',[megamenuController::class, 'updateMegaMenu']);
 
Route::resource('productsDefaultProperty', productsDefaultPropertyController::class);
Route::get('products/properties', [productsDefaultPropertyController::class,'getAll']);

Route::get('socialNetwork', [socialNetworkController::class,'index']);

Route::resource('banner', bannerscontroller::class);
Route::get('banners/getSliders', [bannerscontroller::class,'getSliders']);

//article
Route::get('article', [articleController::class, 'index']);
Route::get('article/{article}', [articleController::class, 'show']);
Route::get('popular_articles', [articleController::class, 'getPopularArticles']);
Route::get('related_articles', [articleController::class, 'getRelatedArticles']);
Route::resource('articleCategories', articleCateoryController::class);
Route::resource('articleComment', articleCommentController::class);
Route::get('article_comments', [articleCommentController::class,'getArticleComments']);
Route::post('article/seen', [articleController::class,'seen_article']);

//videos
Route::get('video', [videoController::class,"index"]);
Route::get('video/{video}', [videoController::class,"show"]);
Route::get('popular_videos', [videoController::class, 'getPopularVideos']);
Route::resource('videoComment', videoCommentController::class);
Route::resource('videoCategories', videoCateoryController::class);
Route::post('video/seen', [articleController::class,'seen_article']);

//news
Route::get('news', [newsController::class, 'index']);
Route::get('news/{news}', [newsController::class, 'show']);
Route::get('popular_news', [newsController::class, 'getPopularNews']);
Route::resource('newsCategories', newsCateoryController::class);
Route::post('news/seen', [articleController::class,'seen_article']);


Route::get('product', [productController::class, 'index']);
Route::get('getProduct', [productController::class, 'getProductById']);
Route::get('getPartialProduct', [productController::class, 'getPartialProduct']);
Route::post('getProducts', [productController::class, 'getProducts']);
Route::post('majorShopping', [productController::class, 'majorShopping']);
Route::get('product_categories', [productsCategoryController::class, 'getCategories']);
Route::get('amazing_products', [productController::class, 'getAmazinProducts']);
Route::get('product_search', [productController::class, 'product_search']);
Route::get('product/{product}', [productController::class, 'show']);
Route::get('products/seo', [productController::class, 'getProductSeo']);

//api for torob
Route::get('products', [productController::class, 'getProductsForTorob']);
Route::post('products', [productController::class, 'getProductForTorob']);

Route::get('search/productsCar', [productCarTypeController::class,"getCarsWithName"]);
Route::get('productCarCompany', [productCarCompanyController::class, 'index']);
Route::get('productCarType', [productCarTypeController::class, 'index']);
Route::get('productCarYear', [productCarYearController::class, 'index']);
Route::get('productCarModel', [productCarModelController::class, 'index']);

Route::get('productCountryBuilders', [ProductCountryBuildersController::class, 'index']);
Route::get('productsCateory', [productsCategoryController::class, 'index']);
Route::get('productsBrand', [productsBrandController::class, 'index']);

Route::get('similar_products', [productController::class, 'getSimilarProducts']);
Route::get('recent_visits', [productController::class, 'getUserRecentVisits']);
Route::get('product_comment/get', [productCommentController::class,'getProductComments']);
Route::get('NotificationOfWarehouseStock', [productController::class, 'NotificationOfWarehouseStock']);
Route::post('product/compatibility', [productController::class, 'isCompatibility']);
Route::post('product/price_fluctuations', [productController::class, 'price_fluctuations']);
Route::post('product/seen', [productController::class, 'seen_product']);

Route::resource('orders', ordersController::class);
Route::get('shippingMethod', [shippingMethodsController::class,'index']);

Route::post('orders/status',[ordersController::class, 'changeOrderStatus']);
Route::post('orders/freightDeliveryReceipt',[ordersController::class, 'setFreightDeliveryReceipt']);
Route::post('orders/rejection_response',[ordersController::class, 'setReasonRejectionFromAdmin']);
Route::post('orders/rejection_request',[ordersController::class, 'setOrderRejectionFromUser']);
Route::post('orders/officialInvoice',[ordersController::class, 'setOfficialInvoice']);
Route::post('orders/save_deposit_invoice',[ordersController::class, 'saveDepositInvoice']);

Route::get('transactions', [transactionController::class, 'index']);

Route::resource('faqs', faqsController::class);
Route::resource('faqsCategories', faqsCategoryController::class);

Route::resource('shopInfo', shopInfoController::class);

Route::resource('newsletters', NewslettersController::class);
Route::resource('contactus', ContactusController::class);
Route::post('contactus/seen', [ContactusController::class,'seen_message']);

Route::resource('team', teamController::class);

// web api
Route::get('web/home', [HomeController::class, 'webHome']);
Route::get('shop_info', [HomeController::class, 'shop_info']);
Route::resource('favorite', favoriteController::class);

Route::get('web/faqs', [faqsController::class, 'webFaqs']);

Route::post('track-order',[ordersController::class, 'orderTracking']);

Route::post('affiliate',[affiliateHistoryController::class, 'store']);
