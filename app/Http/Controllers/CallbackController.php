<?php

namespace App\Http\Controllers;

use App\Events\RealTimeMessageEvent;
use App\Models\FastPayment;
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

use Shetabit\Multipay\Payment;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;

class CallbackController extends Controller
{
    public function callback(Request $request)
    {
        $paymentConfig = require('./local/config/payment.php');
        $payment = new Payment($paymentConfig);

        if($request->has("Authority")){
            $order_id=$request->get("Authority");
        }

        if($request->has("token")){
            $order_id=$request->get("token");
        }

        $ip=$request->ip();

        if($order_id){
            $order = Orders::where("transactionId",$order_id)->first();
            $user = User::select("id","mobile_number")->where("id",$order->user_id)->first();
            $amount = (int)$order->total;

            switch($order->gateway_pay){
                case 1:
                    //ملی
                    $payment->via('sadad');
                    $gateway="پرداخت الکترونیک سداد";
                    break;

                case 2:
                    //ملت
                    $payment->via('behpardakht');
                    $gateway="به پرداخت ملت";
                    break;

                default:
                    return 404;
                    break;

            }

           try {

                $receipt = $payment->amount($amount)->transactionId($order_id)->verify();

                $order->update([
                    "status"=> 2
                ]);

                $products=Cart::where("order_id",$order->id)->get();
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

                $transaction = Transactions::create([
                    'order_id'=> $order->id,
                    'user_id'=> $order->user_id,
                    'description'=> "پرداخت سفارش | ".$order->order_code,
                    'SaleReferenceId'=> $receipt->getReferenceId(),
                    'SaleOrderId'=> $request->Authority,
                    'gateway_pay'=> $gateway,
                    'type'=> 3,
                    'amount'=> $order->total,
                    'status'=> 2
                ]);

                $res=[
                    "status"=> 0,
                    "message"=> "پرداخت با موفقیت انجام شد."
                ];

                $input_data=array("order-code" => $order->order_code);
                Sms::sendWithPatern($user->mobile_number,"baj7ev8neb6wg5o",$input_data);

                $shopInfo = ShopInfo::latest()->first();
                if($shopInfo){
                    if($shopInfo->support_mobile_number){
                        $input_data2=array("order_id" => $order->order_code,"amount"=> number_format($order->total).' تومان');
                        Sms::sendWithPatern($shopInfo->support_mobile_number,"2zzmn18qxlu8lit",$input_data2);
                    }
                }

                $this->sendNotification([
                    'action' => 'order',
                    'order_code' => $order->order_code,
                    'date' => Carbon::now()->format('Y-m-d H:i:s'),
                    'amount' => number_format($order->total).' تومان',
                    'message' => 'سفارش جدید.'
                ]);

                $this->CommissionCalculation($order,(int)$order->total,$user);

                return view("pages/callback/index",compact("res"));

            } catch (InvalidPaymentException $exception) {

                $res=[
                    "status"=> 1,
                    "message"=> $exception->getMessage()
                ];

                return view("pages/callback/index",compact("res"));

            }
        }else{
            return response()->json([
                'success' => false,
                'statusCode' => 401,
                'message' => "عملیات با خطا مواجه شد.",
            ], Response::HTTP_OK);
        }
    }

    public function transaction(Request $request)
    {
        $paymentConfig = require('./local/config/payment.php');
        $payment = new Payment($paymentConfig);

        if($request->has("Authority")){
            $saleOrderId=$request->get("Authority");
        }

        if($request->has("token")){
            $saleOrderId=$request->get("token");
        }

        if($saleOrderId){
            $transaction = Transactions::where("SaleOrderId",$saleOrderId)->first();

            if(!$transaction){
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => "تراکنش یافت نشد!"
                ], Response::HTTP_OK);
            }

            $order = Orders::where("transactionId",$transaction->order_id)->first();
            $user = User::select("id","mobile_number")->where("id",$transaction->user_id)->first();
            $amount = (int)$transaction->amount;

           try {

                $receipt = $payment->amount($amount)->transactionId($saleOrderId)->verify();

                if($order){
                   $order->update([
                        "status"=> 2
                    ]);

                    $products=Cart::where("order_id",$order->id)->get();
                    foreach($products as $item){
                        $item->update([
                            'status'=> 1
                        ]);
                    }
                }

                $transaction->update([
                    'SaleReferenceId'=> $receipt->getReferenceId(),
                    'type'=> 3,
                    'status'=> 2
                ]);

                $res=[
                    "status"=> 0,
                    "message"=> "پرداخت با موفقیت انجام شد."
                ];

                if($user){
                    $input_data=array("transactionId" => $receipt->getReferenceId());
                    Sms::sendWithPatern($user->mobile_number,"gvahfq8xppi8rmo",$input_data);
                }

                return view("pages/callback/index",compact("res"));

            } catch (InvalidPaymentException $exception) {

                $res=[
                    "status"=> 1,
                    "message"=> $exception->getMessage()
                ];

                return view("pages/callback/index",compact("res"));

            }
        }else{
            return response()->json([
                'success' => false,
                'statusCode' => 401,
                'message' => "عملیات با خطا مواجه شد.",
            ], Response::HTTP_OK);
        }
    }

    public function fastPayment(Request $request)
    {
        $paymentConfig = require('./local/config/payment.php');
        $payment = new Payment($paymentConfig);

        if($request->has("Authority")){
            $transaction_id=$request->get("Authority");
        }

        if($request->has("token")){
            $transaction_id=$request->get("token");
        }

        if($transaction_id){
            $transaction = FastPayment::where("SaleOrderId",$transaction_id)->first();
            $amount = (int)$transaction->amount;

            switch($transaction->gateway_pay){

                case 1:
                    //ملی
                    $payment->via('sadad');
                    $gateway="پرداخت الکترونیک سداد";
                    break;

                case 2:
                    //ملت
                    $payment->via('behpardakht');
                    $gateway="به پرداخت ملت";
                    break;

                default:
                    return 404;
                    break;

            }

           try {

                $receipt = $payment->amount($amount)->transactionId($transaction_id)->verify();

                $transaction->update([
                    "status"=> 2,
                    "gateway_title"=> $gateway
                ]);

                $res=[
                    "status"=> 0,
                    "message"=> "پرداخت با موفقیت انجام شد."
                ];

                $input_data=array("transactionId" => $transaction_id);
                Sms::sendWithPatern($transaction->mobile_number,"gvahfq8xppi8rmo",$input_data);

                $shopInfo = ShopInfo::latest()->first();
                if($shopInfo){
                    if($shopInfo->support_mobile_number){
                        $input_data2=array("transaction_id" => $transaction_id,"amount"=> number_format($amount).'تومان');
                        Sms::sendWithPatern($shopInfo->support_mobile_number,"g7p36blu8yb0m7a",$input_data2);
                    }
                }

                $this->sendNotification([
                    'action' => 'fastPayment',
                    'transaction_id' => $transaction_id,
                    'date' => Carbon::now()->format('Y-m-d H:i:s'),
                    'amount' => number_format($amoun) . ' تومان',
                    'message' => 'پرداخت سریع.'
                ]);

                return view("pages/callback/index",compact("res"));

            } catch (InvalidPaymentException $exception) {

                $res=[
                    "status"=> 1,
                    "message"=> $exception->getMessage()
                ];

                return view("pages/callback/index",compact("res"));

            }
        }else{
            return response()->json([
                'success' => false,
                'statusCode' => 401,
                'message' => "عملیات با خطا مواجه شد.",
            ], Response::HTTP_OK);
        }
    }

    public function wallet(Request $request)
    {
        $paymentConfig = require('./local/config/payment.php');
        $payment = new Payment($paymentConfig);

        if($request->has("token")){
            $order_id=$request->get("token");
        }

        if($order_id){
            $transactions = Transactions::where("SaleOrderId",$order_id)->first();
            $user = User::select("id","mobile_number")->where("id",$transactions->user_id)->first();

            $amount = (int)$transactions->amount;
            $balance=(int)$user->wallet_balance+$amount;

            switch($transactions->gateway_pay){
                case 1:
                    //ملی
                    $payment->via('sadad');
                    $gateway="پرداخت الکترونیک سداد";
                    break;

                case 2:
                    //ملت
                    $payment->via('behpardakht');
                    $gateway="به پرداخت ملت";
                    break;

                default:
                    return 404;
                    break;

            }

           try {

                $receipt = $payment->amount($amount)->transactionId($order_id)->verify();

                $transactions->update([
                    "status"=> 2
                ]);

                $user->update([
                    "wallet_balance"=> $balance
                ]);

                $res=[
                    "status"=> 0,
                    "message"=> "پرداخت با موفقیت انجام شد."
                ];

                $input_data=array("user-name"=> $user->first_name,"balance" => number_format($balance),"amount"=> number_format($amount));
                Sms::sendWithPatern($user->mobile_number,"nayvdxpalvv309j",$input_data);

                $shopInfo = ShopInfo::latest()->first();
                if($shopInfo){
                    if($shopInfo->support_mobile_number){
                        $input_data2=array("transaction_id" => $order_id,"amount"=> number_format($amount).'تومان');
                        Sms::sendWithPatern($shopInfo->support_mobile_number,"g7p36blu8yb0m7a",$input_data2);
                    }
                }

                $this->sendNotification([
                    'action' => 'wallet',
                    'transaction_id' => $order_id,
                    'date' => Carbon::now()->format('Y-m-d H:i:s'),
                    'amount' => number_format($amoun) . ' تومان',
                    'message' => 'شارژ کیف پول'
                ]);

                return view("pages/callback/index",compact("res"));

            } catch (InvalidPaymentException $exception) {

                $res=[
                    "status"=> 1,
                    "message"=> $exception->getMessage()
                ];

                return view("pages/callback/index",compact("res"));

            }
        }else{
            return response()->json([
                'success' => false,
                'statusCode' => 401,
                'message' => "عملیات با خطا مواجه شد.",
            ], Response::HTTP_OK);
        }
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
                      'income'=> $income,
                      'wallet_balance'=> $balance
                    ]);
                }
            }

        }
    }

    public function sendNotification($message)
    {
        Notification::create(['message' => $message]);
        event(new RealTimeMessageEvent($message));
    }
}
