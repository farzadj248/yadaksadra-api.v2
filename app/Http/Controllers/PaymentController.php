<?php

namespace App\Http\Controllers;

use App\Events\RealTimeMessageEvent;
use App\Mail\Mail;
use App\Models\Cart;
use App\Helper\Sms;
use App\Models\User;
use App\Models\Orders;
use App\Models\Product;
use App\Models\ShopInfo;
use App\Models\Transactions;
use App\Models\Discounts;
use App\Models\Notification;
use Illuminate\Support\Carbon;
use App\Models\UsersAddress;
use App\Models\ViewersStatistics;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

use Shetabit\Multipay\Invoice;
use Shetabit\Multipay\Payment;

class PaymentController extends Controller
{

    public function payment(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $ip=$request->ip();

        $validator = Validator::make($request->all(), [
            'order_code' => 'required|numeric',
            'payment_m' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $current_order = Orders::where("order_code",$request->order_code)->first();

        $amount = (int)$current_order->total;

        switch($request->payment_m){
             case 1:
                return $this->melli($user,$current_order,$amount);
                break;

            case 2:
                return $this->mellat($user,$current_order,$amount);
                break;

            case 3:
                return $this->wallet($user,$current_order,$amount,$ip);
                break;

            case 4:
                return $this->credit_purchase($user,$current_order,$amount,$ip);
                break;
        }
    }

    public function paymentTransaction(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'way' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $transaction = Transactions::where("id",$request->id)
        ->where("user_id",$user->id)
        ->first();

        if(!$transaction){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => "تراکنش یافت نشد!"
            ], Response::HTTP_OK);
        }

        if($transaction->status==2){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => "تراکنش پرداخت شده است!"
            ], Response::HTTP_OK);
        }

        $amount = (int)$transaction->amount;

        switch($request->way){
             case 1:
                {
                    $paymentConfig = require('./local/config/payment.php');
                    $payment = new Payment($paymentConfig);
                    $invoice = (new Invoice)->amount($amount);
                    $payment->via('sadad');

                    $res = $payment->callbackUrl("https://api.yadaksadra.com/transaction/verify")->purchase($invoice, function($driver, $transactionId) use ($transaction) {
                        $transaction->update([
                            "SaleOrderId"=> $transactionId,
                            "gateway_pay"=> "پرداخت ایترنتی - سداد"
                        ]);

                    })->pay();

                    if($res){
                        return response()->json([
                            'success' => true,
                            'statusCode' => 200,
                            'message' => "عملیات با موفقیت انجام شد.",
                            'data' => $res
                        ], Response::HTTP_OK);
                    }

                    return response()->json([
                        'success' => false,
                        'statusCode' => 422,
                        'message' => "عملیات با خطا مواجه شد.",
                    ], Response::HTTP_OK);
                }
                break;

            case 2:
                {
                    if((int)$user->credit_purchase_inventory >= (int)$amount) {
                        $current_order=Orders::where("id",$transaction->order_id)->first();
                        if($current_order){
                            $current_order->update([
                                "status"=> 2
                            ]);

                            $transaction->update([
                                'SaleReferenceId'=> $current_order->order_code,
                                'gateway_pay'=> 'پرداخت آنلاین - خرید اعتباری  ',
                                "status"=> 2
                            ]);
                        }else{
                            $transaction->update([
                                'SaleReferenceId'=> rand(1234567890,9999999999),
                                'gateway_pay'=> 'پرداخت آنلاین - خرید اعتباری  ',
                                "status"=> 2
                            ]);
                        }

                        $remaining_balance = (int)$user->credit_purchase_inventory-(int)$amount;

                        $user->update([
                            'credit_purchase_inventory'=> $user->credit_purchase_inventory-$remaining_balance
                        ]);

                        return response()->json([
                            'success' => true,
                            'statusCode' => 201,
                            'message' => 'پرداخت با موفقیت انجام شد.',
                        ], Response::HTTP_OK);
                    }else{
                        return response()->json([
                            'success' => false,
                            'statusCode' => 422,
                            'message' => 'اعتبار شما کافی نیست روش دیگری را انتخاب کنید.',
                        ], Response::HTTP_OK);
                    }
                }
                break;

            case 3:
                {
                    if((int)$user->wallet_balance >= (int)$amount) {
                        $current_order=Orders::where("id",$transaction->order_id)->first();
                        if($current_order){
                            $current_order->update([
                                "status"=> 2
                            ]);

                            $transaction->update([
                                'SaleReferenceId'=> $current_order->order_code,
                                'gateway_pay'=> 'پرداخت آنلاین - کیف پول حساب کاربری',
                                "status"=> 2
                            ]);
                        }else{
                            $transaction->update([
                                'SaleReferenceId'=> rand(1234567890,9999999999),
                                'gateway_pay'=> 'پرداخت آنلاین - کیف پول حساب کاربری',
                                "status"=> 2
                            ]);
                        }

                        $remaining_balance = (int)$user->wallet_balance-(int)$amount;

                        $user->update([
                            'wallet_balance'=> $user->wallet_balance-$remaining_balance
                        ]);

                        return response()->json([
                            'success' => true,
                            'statusCode' => 201,
                            'message' => 'پرداخت با موفقیت انجام شد.',
                        ], Response::HTTP_OK);
                    }else{
                        return response()->json([
                            'success' => false,
                            'statusCode' => 422,
                            'message' => 'موجودی حساب شما کافی نمی باشد،برای ادامه پرداخت کیف پول خود را شارژ کنید.',
                        ], Response::HTTP_OK);
                    }
                }
                break;
        }
    }

    public function zarinpal($user,$order,$amount)
    {
        $paymentConfig = require('./local/config/payment.php');

        $payment = new Payment($paymentConfig);

        $invoice = (new Invoice)->amount($amount);

        $invoice->detail(['via' => "zarinpal"]);

        $payment->via('zarinpal');

        $res = $payment->purchase($invoice, function($driver, $transactionId) use($order) {
            $order->update([
                "transactionId"=> $transactionId,
                "gateway_pay"=> 3
            ]);

        })->pay();

        if($res){
            return response()->json([
                'success' => true,
                'statusCode' => 200,
                'message' => "عملیات با موفقیت انجام شد.",
                'status_py'=> 3,
                'data' => $res
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => false,
            'statusCode' => 422,
            'message' => "عملیات با خطا مواجه شد.",
            'data' => null
        ], Response::HTTP_OK);
    }

    public function mellat($user,$order,$amount)
    {
        $paymentConfig = require('./local/config/payment.php');

        $payment = new Payment($paymentConfig);

        $invoice = (new Invoice)->amount($amount);

        $payment->via('behpardakht');

        return $payment->purchase($invoice, function($driver, $transactionId) {
            $order->update([
                "transactionId"=> $transactionId,
                "gateway_pay"=> 2
            ]);

        })->pay();

        if($res){
            return response()->json([
                'success' => true,
                'statusCode' => 200,
                'message' => "عملیات با موفقیت انجام شد.",
                'status_py'=> 2,
                'data' => $res
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => false,
            'statusCode' => 422,
            'message' => "عملیات با خطا مواجه شد.",
            'data' => null
        ], Response::HTTP_OK);
    }

    public function melli($user,$order,$amount){
        $paymentConfig = require('./local/config/payment.php');

        $payment = new Payment($paymentConfig);

        $invoice = (new Invoice)->amount($amount);

        $payment->via('sadad');

        $res = $payment->purchase($invoice, function($driver, $transactionId) use ($order) {
            $order->update([
                "transactionId"=> $transactionId,
                "gateway_pay"=> 1
            ]);

        })->pay();

        if($res){
            return response()->json([
                'success' => true,
                'statusCode' => 200,
                'message' => "عملیات با موفقیت انجام شد.",
                'status_py'=> 1,
                'data' => $res
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => false,
            'statusCode' => 422,
            'message' => "عملیات با خطا مواجه شد.",
            'data' => null
        ], Response::HTTP_OK);
    }


    public function wallet($user,$current_order,$amount,$ip=null)
    {
        if($current_order->status==0){

            if((int)$user->wallet_balance >= (int)$amount) {

                $transactions = Transactions::where('order_id',$current_order->order_code)
                ->where("status",1)
                ->first();

                if($transactions){
                    $transactions->update([
                        'amount'=> $amount,
                        'SaleOrderId'=> $current_order->order_code,
                        "status"=> 2
                    ]);
                }else{
                    $current_order->update([
                        "gateway_pay"=> 3
                    ]);

                    do {
                       $refrence_id = mt_rand( 10000, 99999 ).mt_rand( 100, 999 );
                    } while ( Transactions::where( 'SaleReferenceId', $refrence_id )->exists() );

                    $transactions=Transactions::create([
                        'user_id'=> $user->id,
                        'order_id'=> $current_order->order_code,
                        'SaleReferenceId'=> $refrence_id,
                        'gateway_pay'=> 'پرداخت آنلاین - کیف پول حساب کاربری',
                        "description"=> "مبلغ سفارش - پرداخت موفق",
                        'type'=> 1,
                        'amount'=> $amount,
                        "status"=> 2
                    ]);
                }

                $current_order->update([
                    "status"=> 2
                ]);

                $remaining_inventory = (int)$user->wallet_balance-(int)$amount;

                $user->update([
                    'wallet_balance'=> $remaining_inventory
                ]);


                $products=Cart::where("order_id",$current_order->id)->get();
                foreach($products as $item){
                    $item->update([
                        'status'=> 1
                    ]);

                    $product = Product::where("id",$item->product_id)->first();
                    if($product) {
                        $count = (int)$product->number_sales+1;
                        $product->update([
                            "number_sales" => $count
                        ]);

                        ViewersStatistics::create([
                            "type"=> "product",
                            "post_id"=> $product->id,
                            "price"=> $product->main_price,
                            "ip_address"=> $ip,
                            "action"=> "buy"
                        ]);
                    }
                }


                $this->CommissionCalculation($current_order,$amount,$user);

                $input_data=array("order-code" => $current_order->order_code);
                Sms::sendWithPatern($user->mobile_number,"baj7ev8neb6wg5o",$input_data);

                $shopInfo = ShopInfo::latest()->first();
                if($shopInfo){
                    if($shopInfo->support_mobile_number){
                        $input_data2=array("order_id" => $current_order->order_code,"amount"=> number_format($current_order->total).' تومان');
                        Sms::sendWithPatern($shopInfo->support_mobile_number,"2zzmn18qxlu8lit",$input_data2);
                    }
                }

                $this->sendNotification('event',[
                    'action' => 'order',
                    'id' => $current_order->id,
                    'date' => Carbon::now()->format('Y-m-d H:i:s'),
                    'message' => 'سفارش جدید دریافت شد.'
                ]);

                return response()->json([
                    'success' => true,
                    'statusCode' => 201,
                    'message' => 'پرداخت با موفقیت انجام شد.',
                    'status_py'=> 2,
                    'data'=> 'https://yadaksadra.com/profile/orders/'.$current_order->order_code
                ], Response::HTTP_OK);
            }else{
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => 'موجودی حساب شما کافی نمی باشد،برای ادامه پرداخت کیف پول خود را شارژ کنید.',
                ], Response::HTTP_OK);
            }

        }

        return response()->json([
            'success' => false,
            'statusCode' => 422,
            'message' => "عملیات ناموفق بود"
        ], Response::HTTP_OK);
    }


    public function credit_purchase($user,$current_order,$amount,$ip=null)
    {
        if($current_order->status==0){

            if($user->documents_status==2){

                if((int)$user->credit_purchase_inventory >= (int)$amount) {

                    $transactions = Transactions::where('order_id',$current_order->order_code)
                    ->where("status",1)
                    ->first();

                    if($transactions){
                        $transactions->update([
                            'amount'=> $amount,
                            'SaleOrderId'=> $current_order->order_code,
                            "status"=> 2
                        ]);
                    }else{
                        $current_order->update([
                            "gateway_pay"=> 4
                        ]);

                        Transactions::create([
                            'user_id'=> $user->id,
                            'order_id'=> $current_order->order_code,
                            'SaleOrderId'=> $current_order->order_code,
                            'gateway_pay'=> 'پرداخت آنلاین - خرید اعتباری  ',
                            "description"=> "پرداخت سفارش | ".$current_order->order_code,
                            'type'=> 1,
                            'amount'=> $amount,
                            "status"=> 2
                        ]);
                    }

                    $current_order->update([
                        "status"=> 2
                    ]);

                    $remaining_inventory = (int)$user->credit_purchase_inventory-(int)$amount;

                    $user->update([
                        'credit_purchase_inventory'=> $remaining_inventory
                    ]);

                    $products=Cart::where("order_id",$current_order->id)->get();
                    foreach($products as $item){
                        $item->update([
                            'status'=> 1
                        ]);

                        $product = Product::where("id",$item->product_id)->first();
                        if($product) {
                            $count = (int)$product->number_sales+1;
                            $product->update([
                                "number_sales" => $count
                            ]);

                            ViewersStatistics::create([
                                "type"=> "product",
                                "post_id"=> $product->id,
                                "ip_address"=> $ip,
                                "action"=> "buy"
                            ]);
                        }
                    }

                    $this->CommissionCalculation($current_order,$amount,$user);

                    $input_data=array("order-code" => $current_order->order_code);
                    Sms::sendWithPatern($user->mobile_number,"baj7ev8neb6wg5o",$input_data);

                    $shopInfo = ShopInfo::latest()->first();
                    if($shopInfo){
                        if($shopInfo->support_mobile_number){
                            $input_data2=array("order_id" => $current_order->order_code,"amount"=> number_format($current_order->total).' تومان');
                            Sms::sendWithPatern($shopInfo->support_mobile_number,"2zzmn18qxlu8lit",$input_data2);
                        }
                    }

                    $this->sendNotification('event',[
                        'action' => 'order',
                        'id' => $current_order->id,
                        'date' => Carbon::now()->format('Y-m-d H:i:s'),
                        'message' => 'سفارش جدید دریافت شد.'
                    ]);

                    return response()->json([
                        'success' => true,
                        'statusCode' => 201,
                        'message' => 'پرداخت با موفقیت انجام شد.',
                        'status_py'=> 2,
                        'data'=> 'https://yadaksadra.com/profile/orders/'.$current_order->order_code
                    ], Response::HTTP_OK);
                }else{
                    return response()->json([
                        'success' => false,
                        'statusCode' => 422,
                        'message' => 'اعتبار شما کافی نمی باشد.',
                    ], Response::HTTP_OK);
                }

            }else{

                if($user->credit_purchase_type==0){
                    return response()->json([
                        'success' => false,
                        'statusCode' => 422,
                        'message' => 'جهت فعال سازی پرداخت اعتباری به پروفایل کاربری خود رجوع کنید.',
                    ], Response::HTTP_OK);
                }else{

                    return response()->json([
                        'success' => false,
                        'statusCode' => 422,
                        'message' => 'تا زمان تأیید مدارک شکیبا باشید.',
                    ], Response::HTTP_OK);

                }

            }

        }

        return response()->json([
            'success' => false,
            'statusCode' => 422,
            'message' => "عملیات ناموفق بود"
        ], Response::HTTP_OK);
    }


    public function chargeWallet(Request $request)
    {
       $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'gateway' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        switch($request->gateway){
            case 1:
                $via="sadad";
                break;

            case 2:
                $via="behpardakht";
                break;
        }

        $paymentConfig = require('./local/config/payment.php');
        $payment = new Payment($paymentConfig);
        $invoice = (new Invoice)->amount($request->amount);
        $payment->via($via);

        $res = $payment->callbackUrl('https://api.yadaksadra.com/wallet/verify')->purchase($invoice, function($driver, $transactionId) use ($user,$request) {

            $referenceId=$user->user_id.rand(1111111,99999999);

            Transactions::create([
                'order_id'=> 0,
                'user_id'=> $user->user_id,
                'description'=> "شارژ کیف پول | ".$referenceId,
                'SaleReferenceId'=> $referenceId,
                'SaleOrderId'=> $transactionId,
                'gateway_pay'=> $request->gateway,
                'type'=> 2,
                'amount'=> $request->amount,
                'status'=> 1
            ]);

        })->pay();

        if($res){
            return response()->json([
                'success' => true,
                'statusCode' => 200,
                'message' => "عملیات با موفقیت انجام شد.",
                'data' => $res
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => false,
            'statusCode' => 422,
            'message' => "عملیات با خطا مواجه شد.",
            'data' => null
        ], Response::HTTP_OK);
    }


    public function getFinalAmount($user,$current_order)
    {
        $current_order = Orders::where("status",0)->where("user_id",$user->id)->first();

        $discount_code = Discounts::select("code","type","value","expire_date")->where("id",$current_order->discount_code_id)->first();

        $order_items=Cart::where('carts.status',0)
            ->where('carts.order_id',$current_order->id)
            ->select('saved_off','saved_price','quantity')
            ->get();

        $sum=0;
        $discount=0;
        $coupon_price=0;
        $total=0;
        $final=0;
        $coupon="";
        $hasCoupon = false;

        foreach ($order_items as $item){
            $sum+=(int)$item->saved_price * $item->quantity;
            $discount+=((int)$item->saved_price*((int)$item->saved_off)/100) * $item->quantity;
        }

        if($current_order->sending_method == 1 or $current_order->sending_method == 2){
            $total = $sum - $discount + $current_order->sending_amount;
        }else{
            $total = $sum - $discount;
        }

        if($discount_code){
            if($this->isValid($discount_code->expire_date)){
                if($discount_code->type == 1){
                    $coupon_price=$total*(int)$discount_code->value/100;
                    $final=$total*((100-(int)$discount_code->value)/100);
                }else{
                    $coupon_price=$discount_code->value;
                    $final=$total-(int)$discount_code->value;
                }

                $coupon=$discount_code->code;

                $hasCoupon=true;
            }else{
                $final=$total;

                $current_order->update([
                    'discount_code_id'=> null
                ]);
            }
        }else{
            $final=$total;
        }

        $current_order->update([
            'total'=> $final,
            'discount'=> $discount+$coupon_price
        ]);

        return $final;
    }

    public function CommissionCalculation($order,$amount,$user)
    {
        if($user->affiliate_id){

            $purchase_profit = 0;
            $shopInfo = ShopInfo::latest()->first();
            $marketer = User::select("id","role","income","wallet_balance")->where("id",$user->affiliate_id)->first();

            if($marketer){

                switch($marketer->role){
                    case "Marketer":

                        switch($user->role){
                            case "Marketer":
                                if($shopInfo->marketer_percent_purchase){
                                    $purchase_profit = (int)$amount *($shopInfo->marketer_percent_purchase/100);
                                }
                            break;

                            default:
                                //other role
                                 // Product price difference

                                $cart=Cart::leftJoin('products', function ($query) {
                                    $query->on('products.id', '=', 'carts.product_id');
                                })
                                ->where("carts.order_id",$order->id)
                                ->select('carts.quantity','carts.grade','products.main_price', 'products.main_price_2', 'products.main_price_3', 'products.custom_price',
                                'products.custom_price_2', 'products.custom_price_3', 'products.market_price', 'products.market_price_2', 'products.market_price_3')
                                ->get();

                                switch ($user->role) {
                                    case 'Saler':

                                        foreach($cart as $item){

                                            switch($item->grade){
                                                case "Main":
                                                    // main_price
                                                    $purchase_profit += $item->quantity * ( (int)$item->main_price_3 - (int)$item->main_price_2);
                                                    break;

                                                case "Custom":
                                                    // custom_price
                                                    $purchase_profit += $item->quantity * ((int)$item->custom_price_3 - (int)$item->custom_price_2);
                                                    break;

                                                case "Market":
                                                    //market_price
                                                    $purchase_profit += $item->quantity * ((int)$item->market_price_3 - (int)$item->market_price_2);
                                                    break;
                                            }

                                        }

                                    break;

                                    default:

                                        foreach($cart as $item){

                                            switch($item->grade){
                                                case "Main":
                                                    // main_price
                                                    $purchase_profit += $item->quantity * ((int)$item->main_price - (int)$item->main_price_2);
                                                    break;

                                                case "Custom":
                                                    // custom_price
                                                    $purchase_profit += $item->quantity * ((int)$item->custom_price - (int)$item->custom_price_2);
                                                    break;

                                                case "Market":
                                                    //market_price
                                                    $purchase_profit += $item->quantity * ((int)$item->market_price - (int)$item->market_price_2);
                                                    break;
                                            }

                                        }

                                    break;
                                }

                            break;
                        }

                    break;

                    case "Saler":
                        if($user->role=="Marketer" || $user->role=="Saler"){
                            if($shopInfo->other_percent_purchase){
                                $purchase_profit = (int)$amount *($shopInfo->other_percent_purchase/100);
                            }
                        }else{
                            //other role

                            $cart=Cart::leftJoin('products', function ($query) {
                                $query->on('products.id', '=', 'carts.product_id');
                            })
                            ->where("carts.order_id",$order->id)
                            ->select('carts.quantity','carts.grade','products.main_price', 'products.main_price_2', 'products.main_price_3', 'products.custom_price',
                            'products.custom_price_2', 'products.custom_price_3', 'products.market_price', 'products.market_price_2', 'products.market_price_3')
                            ->get();

                            foreach($cart as $item){

                                switch($item->grade){
                                    case "Main":
                                        // main_price
                                        $purchase_profit += $item->quantity * ((int)$item->main_price - (int)$item->main_price_2);
                                        break;

                                    case "Custom":
                                        // custom_price
                                        $purchase_profit += $item->quantity * ((int)$item->custom_price - (int)$item->custom_price_2);
                                        break;

                                    case "Market":
                                        //market_price
                                        $purchase_profit += $item->quantity * ((int)$item->market_price - (int)$item->market_price_2);
                                        break;
                                }

                            }

                        }

                    break;

                    default:
                        //Other role
                        if($shopInfo->other_percent_purchase){
                            $purchase_profit = (int)$amount *($shopInfo->other_percent_purchase/100);
                        }
                    break;
                }


                if($purchase_profit>0){

                    $income = (int)$marketer->income + $purchase_profit;
                    $balance = (int)$marketer->wallet_balance + $purchase_profit;

                    $marketer->update([
                      "income"=> $income,
                      "wallet_balance"=> $balance
                    ]);
                }
            }

        }
    }

    public function isValid($expire_date) {
        $now = Carbon::now();
        $created_at=$now->toDateTimeString();

        $date1 = Carbon::createFromFormat('Y-m-d H:i:s', $expire_date);
        $date2 = Carbon::createFromFormat('Y-m-d H:i:s', $created_at);

        if($date1->eq($date2)){
            return true;
        }else{
            if($date1->gt($date2)){
                return true;
            }else{
                return false;
            }
        }
    }

    public function sendInvoiceToEmail(Request $request){
        $details = [
            'view'=> 'mail.invoice',
            'subject' => $request->subject,
            'body' => $request->body
        ];

        \Mail::to($request->email)->send(new Mail($details));

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.'
        ], Response::HTTP_OK);
    }

    public function sendNotification($message)
    {
        $notification = Notification::create(['message' => $message]);
        event(new RealTimeMessageEvent('event', $notification));
    }

}
