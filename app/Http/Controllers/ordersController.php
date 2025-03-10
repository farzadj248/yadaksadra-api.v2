<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\User;
use App\Models\Orders;
use App\Models\Transactions;
use App\Models\Product;
use App\Helper\Sms;
use App\Http\Resources\OrderResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class ordersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $orders1=Orders::leftJoin('users', function ($query) {
            $query->on('users.id', '=', 'orders.user_id');
        })
        ->select('orders.id','orders.order_code','orders.total','orders.created_at','orders.status','users.first_name','users.last_name')
        ->Where("orders.status",'!=',0);

        if($request->q){
            $orders1->whereRaw('concat(orders.order_code,users.first_name) like ?', "%{$request->q}%");
        }
        
        $orders = $orders1->orderBy("orders.id","desc")->paginate(10);
        return OrderResource::collection($orders);
    }

    public function getOrders(Request $request)
    {
        $user = auth()->user();

        $orders=Orders::select('id','order_code','total','status','created_at')
            ->orderBy("id","desc")
            ->where("user_id",$user->id)
            ->whereIn("status",$request->status)
            ->paginate(10);

        $orders->setCollection(
            $orders->getCollection()->map(function($item) use ($request){
                $item['products'] = Cart::leftJoin('products', function ($query) {
                    $query->on('products.id', '=', 'carts.product_id');
                })
                    ->leftJoin('products_images', function ($query) {
                        $query->on('products_images.product_id', '=', 'carts.product_id')
                            ->whereRaw('products_images.id IN (select MAX(a2.id) from products_images as a2 join products as u2 on u2.id = a2.product_id group by u2.id)');
                    })
                    ->where('carts.order_id',$item->id)
                    ->select('products.id as product_id','products.title','products.slug','products_images.url as image')
                    ->get();

                return $item;
            })
        );

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $orders
        ], Response::HTTP_OK);
    }

    public function show($orderId)
    {
        $order = Orders::with(['items.product.image'])
        ->findOrFail($orderId);

        $transactions = Transactions::where("order_id",$order->id)->first();

        $user = User::where("id",$order->user_id)->first();

        if($order->isRejected == 0){
            $now = Carbon::now();
            $created_at=$now->toDateTimeString();

            $formatted_dt1=Carbon::parse($order->created_at);

            $formatted_dt2=Carbon::parse($created_at);

            $date_diff=$formatted_dt1->diffInDays($formatted_dt2);

            if($date_diff>=7){
               $order->update([
                    "isRejected"=> 4
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'order' => $order,
                'user' => $user,
                'transactions'=> $transactions
            ]
        ], Response::HTTP_OK);
    }

    public function getUserOrder(Orders $order)
    {
        $orderItems = Cart::where("order_id",$order->id)->leftJoin('products', function ($query) {
            $query->on('products.id', '=', 'carts.product_id');
        })
        ->leftJoin('products_images', function ($query) {
            $query->on('products_images.product_id', '=', 'carts.product_id');
        })
        ->select('carts.*','products.title','products_images.url')
        ->get();

        $user = User::where("id",$order->user_id)
        ->select("first_name",'last_name','id','email','mobile_number')
        ->first();

        $marketer = User::where("id",$order->marketer_id)
        ->select("personnel_code","first_name",'last_name','id')
        ->first();

        $transactions = Transactions::where("order_id",$order->order_code)->get();

        if($order->isRejected == 0){
            $now = Carbon::now();
            $created_at=$now->toDateTimeString();

            $formatted_dt1=Carbon::parse($order->created_at);

            $formatted_dt2=Carbon::parse($created_at);

            $date_diff=$formatted_dt1->diffInDays($formatted_dt2);

            if($date_diff>=7){
               $order->update([
                    "isRejected"=> 4
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'order' => $order,
                'user' => $user,
                'marketer' => $marketer,
                'orderItems'=> $orderItems,
                'transactions'=> $transactions
            ]
        ], Response::HTTP_OK);
    }

    /*
    change order Status
    */
    public function changeOrderStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'status' => 'required|numeric|in:1,2,3,4,5,6',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $order = Orders::where("order_code",$request->id)->first();
        $order->update([
            "status" => $request->status
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    /*update Product Stock*/
    public function updateProductStock(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'order_code' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $order_code = $request->order_code;
        $order = Orders::where("order_code",$request->order_code)->select("id","user_id","total")->first();
        $user_id=$order->user_id;
        $user = User::where("id",$user_id)->first();
        $user_name = $user->first_name.' '.$user->last_name;

        $cart_item = Cart::where("id",$request->id)->first();
        if($cart_item->instock==1){
          $returm_price = (int)$cart_item->saved_price+(int)$user->wallet_balance;

            $cart_item->update([
                "instock" => 0
            ]);

            $cart_count = Cart::where("order_id",$order->id)->get()->count();

            if($cart_count==1){
                $returm_price = (int)$user->wallet_balance+(int)$order->total;
            }

            $user->update([
                "wallet_balance" => $returm_price
            ]);
        }

        $product = Product::where("id",$cart_item->product_id)->select("title")->first();
        $title = $product->title;

        $number=$user->mobile_number;
        $message = "مشتری گرامی:".$user_name."
        با عرض پوزش
        محصول سفارشی شما ".$title."
        به شماره سفارش: ".$order_code."
        موجود نمی باشد.
        مبلغ پرداختی در کیف پول شما شارژ شد.";

        Sms::send($number,$message);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    /*
    set TrackingNumber for order
    */
    public function setFreightDeliveryReceipt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'file' => 'nullable|String',
            'postTrackingCode'=> 'nullable|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $order = Orders::where("order_code",$request->id)->first();
        $order->update([
            "postal_receipt" => [
                'file' => $request->file,
                'tracking_number' => $request->postTrackingCode
            ]
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    /* order rejection */
    public function setOrderRejectionFromUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'message' => 'required|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $order = Orders::where("order_code",$request->id)->first();

        $new_order = $order->replicate();
        $new_order->created_at = Carbon::now();
        $new_order->save();

        $order->update([
            "isRejected" => 1,
        ]);

        $new_order->update([
            "order_code" => (int)$order->order_code + 1,
            "status"=> 8,
            "isRejected"=> 1,
            "reason_rejection" => $request->message
        ]);

        $cart_items = Cart::where("order_id",$order->id)->get();

        foreach($cart_items as $item){
            $cartNew = Cart::create([
                'product_id' => $item->id,
                'order_id' => $new_order->id,
                'quantity' => $item->quantity,
                'uuid'=> $item->uuid,
                'grade_type'=> $item->grade_type,
                'user_role'=> $item->user_role,
                'saved_price'=> $item->saved_price,
                'saved_off'=> $item->saved_off,
                'status' => $item->status
            ]);
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'درخواست شما با موفقیت ثبت شد.',
        ], Response::HTTP_OK);
    }

    public function setReasonRejectionFromAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'message' => 'required|string',
            'status' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $order = Orders::where("order_code",$request->id)->first();
        $user = User::select("mobile_number")->where("id",$order->user_id)->first();

        if($request->status==2){
           $order->update([
                "status"=> 7,
                "response_reason_rejection" => $request->message,
                "isRejected" => 2
            ]);
        }else{
            $order->update([
                "response_reason_rejection" => $request->message,
                "isRejected" => 3
            ]);
        }

        $numbers=$user->mobile_number;
        $body=$request->message."
        کد  سفارش : ".$order->order_code."
        ";

        Sms::send($numbers,$body);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    /*save Deposit Invoice*/
    public function saveDepositInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_code' => 'required|numeric',
            'amount' => 'required|String',
            'code' => 'required|String',
            'gateway_pay' => 'required|String',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $order = Orders::where("order_code",$request->order_code)->first();

        $transactions = Transactions::create([
            "user_id"=>$order->user_id,
            "order_id"=>$request->order_code,
            "description"=>"پرداخت مبلغ کالاهای مرجوعی | ".$request->order_code,
            "SaleOrderId"=>$request->code,
            "gateway_pay"=>$request->gateway_pay,
            "type"=> 2,
            "amount"=>$request->amount,
            "status"=>2
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data'=> $transactions
        ], Response::HTTP_OK);
    }
    /*
    set Official Invoice for User
    */
    public function setOfficialInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'file' => 'required|String',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $order = Orders::where("order_code",$request->id)->first();
        $order->update([
            "Official_file" => $request->file,
            "isOfficial" => 2
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    public function orderTracking(Request $request)
    {
        $order = Orders::where("order_code",$request->order_code)->first();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data'=> $order
        ], Response::HTTP_OK);
    }

    public function updateOrderProducts($id)
    {
        $order=Orders::findOrFail($id);
        if(!$order){
            return response()->json([
                'success' => false,
                'statusCode' => 401,
                'message' => "اطلاعات ارسالی نامعتبر است!",
                'data'=> $order
            ], Response::HTTP_OK);
        }

        $user=User::where("id",$order->user_id)->select("role","uuid")->first();

        $finalAmount=0;
        $discount=0;

        $id = request()->id ?? '';
        $grade = request()->grade ?? 'Main';
        $quantity = request()->quantity ?? 1;

        $product = Product::findOrFail($id);
        if(!$product) {
            return response()->json([
                'success' => false,
                'message' => "محصول با این شناسه یافت نشد.",
            ], 422);
        }

            $price="";
            $off="";

            switch($user->role){
                case "Marketer":
                    switch($grade)
                    {
                        case "Main":
                            $price=$product->main_price_2;
                            $off=$product->main_off_2;

                            if ($product->main_price_2 > 0) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "برای محصول انتخابی قیمت ثبت نشده است.",
                                ], 422);
                            }

                            if ($product->main_inventory_2 < $quantity) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "موجودی محصول انتخابی ". $product->main_inventory_2 ." عدد می باشد.",
                                ], 422);
                            }
                            break;

                        case "Custom":
                            $price=$product->custom_price_2;
                            $off=$product->custom_off_2;

                            if ($product->custom_price_2 > 0) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "برای محصول انتخابی قیمت ثبت نشده است.",
                                ], 422);
                            }

                            if ($product->custom_inventory_2 < $quantity) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "موجودی محصول انتخابی " . $product->custom_inventory_2 . " عدد می باشد.",
                                ], 422);
                            }
                            break;

                        case "Marketer":
                            $price=$product->market_price_2;
                            $off=$product->market_off_2;

                            if ($product->market_price_2 > 0) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "برای محصول انتخابی قیمت ثبت نشده است.",
                                ], 422);
                            }

                            if ($product->market_inventory_2 < $quantity) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "موجودی محصول انتخابی " . $product->market_inventory_2 . " عدد می باشد.",
                                ], 422);
                            }
                            break;
                    }
                break;

                case "Saler":
                    switch($grade)
                    {
                        case "Main":
                            $price=$product->main_price_3;
                            $off=$product->main_off_3;

                            if ($product->main_price_3 > 0) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "برای محصول انتخابی قیمت ثبت نشده است.",
                                ], 422);
                            }

                            if ($product->main_inventory_3 < $quantity) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "موجودی محصول انتخابی " . $product->main_inventory_3 . " عدد می باشد.",
                                ], 422);
                            }
                            break;

                        case "Custom":
                            $price=$product->custom_price_3;
                            $off=$product->custom_off_3;

                            if ($product->custom_price_3 > 0) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "برای محصول انتخابی قیمت ثبت نشده است.",
                                ], 422);
                            }

                            if ($product->custom_inventory_3 < $quantity) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "موجودی محصول انتخابی " . $product->custom_inventory_3 . " عدد می باشد.",
                                ], 422);
                            }
                            break;

                        case "Marketer":
                            $price=$product->market_price_3;
                            $off=$product->market_off_3;

                            if ($product->market_price_3 > 0) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "برای محصول انتخابی قیمت ثبت نشده است.",
                                ], 422);
                            }
                            
                            if ($product->market_inventory_3 < $quantity) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "موجودی محصول انتخابی " . $product->market_inventory_3 . " عدد می باشد.",
                                ], 422);
                            }
                            break;
                    }
                    break;

                default :
                    switch($grade)
                    {
                        case "Main":
                            $price=$product->main_price;
                            $off=$product->main_off;

                            if ($product->main_price > 0) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "برای محصول انتخابی قیمت ثبت نشده است.",
                                ], 422);
                            }

                            if ($product->main_inventory < $quantity) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "موجودی محصول انتخابی " . $product->main_inventory . " عدد می باشد.",
                                ], 422);
                            }
                            break;

                        case "Custom":
                            $price=$product->custom_price;
                            $off=$product->custom_off;

                            if ($product->custom_price > 0) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "برای محصول انتخابی قیمت ثبت نشده است.",
                                ], 422);
                            }
                            
                            if ($product->custom_inventory < $quantity) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "موجودی محصول انتخابی " . $product->custom_inventory . " عدد می باشد.",
                                ], 422);
                            }
                            break;

                        case "Marketer":
                            $price=$product->market_price;
                            $off=$product->market_off;

                            if ($product->market_price > 0) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "برای محصول انتخابی قیمت ثبت نشده است.",
                                ], 422);
                            }

                            if ($product->market_inventory < $quantity) {
                                return response()->json([
                                    'success' => false,
                                    'message' => "موجودی محصول انتخابی " . $product->market_inventory . " عدد می باشد.",
                                ], 422);
                            }
                            break;
                    }
                break;
            }

            $finalAmount=$finalAmount+$quantity*($price*((100-(int)$off)/100));
            $discount=$discount+$quantity*($price*($off/100));

            $cart=Cart::where("order_id",$order->id)
            ->where("product_id",$id)
            ->first();

            if($price!=0){
                if($cart){
                    $cart->update([
                        "quantity"=> $quantity,
                        "saved_price"=> $price,
                        "saved_off"=> $off,
                        "grade"=> $grade,
                    ]);
                }else{
                    Cart::create([
                        "uuid"=> $user->uuid,
                        "product_id"=> $id ,
                        "order_id"=> $order->id,
                        "quantity"=> $quantity,
                        "saved_price"=> $price,
                        "saved_off"=> $off,
                        "grade"=> $grade,
                        "user_role"=> $user->role,
                        "status"=> 1
                    ]);
                }
            }

        $summery=$this->orderSummery($order);

        $order->update([
            "total" => $summery['total'],
            "discount" => $summery['discount']
        ]);

        $this->updateTransaction($order,$summery['total']);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    public function removeProductFromOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orderId' => 'required|numeric|exists:orders,id',
            'cartId' => 'required|numeric|exists:carts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $order = Orders::findOrFail($request->orderId);

        $cart=Cart::findOrFail($request->cartId);
        if(!$cart){
            return response()->json([
                'success' => false,
                'statusCode' => 401,
                'message' => [
                    'cart' => 'محصول یافت نشد'
                ],
            ], 422);
        }

        $cart->delete();

        $summery=$this->orderSummery($order);

        $order->update([
            "total" => $summery['total'],
            "discount" => $summery['discount']
        ]);

        $this->updateTransaction($order,$summery['total']);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    public function cancellOrderByAdmin(Request $request,$orderCode)
    {
        $validator = Validator::make($request->all(), [
            'nopay'=> 'nullable|numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $order=Orders::where("order_code", $orderCode)->first();
        if(!$order){
            return response()->json([
                'success' => false,
                'message' => "اطلاعات ارسالی نامعتبر است!",
            ], 422);
        }

        $order->update([
            "status"=> 6
        ]);

        // if($request->nopay&&$request->nopay==1){
            //بدون بازگشت وجه
        // }else{
            $user=User::where("id",$order->user_id)->first();
            $user_name=$user->first_name." ".$user->last_name;

            $user->update([
                "wallet_balance"=> $user->wallet_balance+$order->total
            ]);

            $number="09361544927";
            $message = "مشتری گرامی:".$user_name."
            سفارش شما
            به شماره: ".$order->order_code."
            لغو شد.
            مبلغ پرداختی در کیف پول شما شارژ شد.";

            Sms::send($number,$message);
        // }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    public function orderSummery($order)
    {

        $order_items=Cart::select('saved_off','saved_price','quantity')
            ->where("order_id",$order->id)
            ->get();

        $sum=0;
        $discount=0;
        $total=0;
        $coupon=0;

        foreach ($order_items as $item){
            $sum+=(int)$item->saved_price * $item->quantity;
            $discount+=((int)$item->saved_price*((int)$item->saved_off)/100) * $item->quantity;
        }

        if($order->discount_code_id){
            $coupon=$order->discounted_amount;
        }

        $total_discount=$discount + $coupon;

        $total = $sum - $total_discount;

        return [
            'discount'=> $total_discount,
            'total'=> $total,
        ];
    }

    public function updateTransaction($order,$amount){
        $transaction=Transactions::where("order_id",$order->id)
        ->where("status",1)
        ->first();

        if($transaction){
            $transaction->update([
                "amount"=> $amount
            ]);
        }else{
            Transactions::create([
                "user_id"=>$order->user_id,
                "order_id"=>$order->id,
                "description"=> "اصلاحیه سفارش شماره ".$order->order_code,
                "amount"=> $amount,
                "type"=> 3
            ]);
        }
    }

    public function verifyOrder($orderCode)
    {
        $order = Orders::where("order_code", $orderCode)->first();
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => [
                    'order' => ["سفارشی  با این شناسه یافت نشد."]
                ],
            ], 422);
        }

        $order->update([
            "status" => 2
        ]);
        
        $body="مشتری گرامی
سفارش شما تأیید شد.
شماره پیگیری : ". $order->order_code;

        Sms::send($order->user->mobile,$body);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

}
