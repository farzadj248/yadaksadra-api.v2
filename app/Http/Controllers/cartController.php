<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Orders;
use App\Models\ShopInfo;
use App\Models\Discounts;
use App\Models\UsersAddress;
use App\Models\Product;
use Illuminate\Support\Carbon;
use App\Models\ProductsImages;
use App\Models\ShippingMethods;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;

class cartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $cart = $this->getProductsCart($request->uuid);
        $summery = $this->getInitialsSummery($request->uuid);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'products'=> $cart,
                'summery'=> $summery
            ]
        ], Response::HTTP_OK);
    }

    public function getProductsCart($uuid)
    {
        $cart=Cart::leftJoin('products', function ($query) {
            $query->on('products.id', '=', 'carts.product_id');
        })
        ->leftJoin('products_images', function ($query) {
            $query->on('products_images.product_id', '=', 'carts.product_id')
                ->whereRaw('products_images.id IN (select MAX(a2.id) from products_images as a2 join products as u2 on u2.id = a2.product_id group by u2.id)');
        })
        ->where('carts.status',0)
        ->where('carts.uuid',$uuid)
        ->select('carts.id as cart_id','products.id as product_id','products.slug','products.title','products.category_name',
            'carts.id as cart_id','carts.quantity','carts.grade','user_role as role','products_images.url as image',
            "products.main_inventory", "products.market_inventory", "products.custom_inventory","products.main_inventory_2",
            "products.market_inventory_2", "products.custom_inventory_2","products.main_inventory_3", "products.market_inventory_3", "products.custom_inventory_3",
            'products.main_price','products.main_price_2', 'products.main_price_3','products.custom_price',
            'products.custom_price_2','products.custom_price_3','products.market_price','products.market_price_2','products.market_price_3',
            'products.main_off', 'products.market_off', 'products.custom_off','products.main_off_2', 'products.market_off_2', 'products.custom_off_2',
            'products.main_off_3', 'products.market_off_3', 'products.custom_off_3',
            'products.main_minimum_purchase', 'products.main_minimum_purchase_2', 'products.main_minimum_purchase_3',
            'products.market_minimum_purchase', 'products.market_minimum_purchase_2', 'products.market_minimum_purchase_3',
            'products.custom_minimum_purchase', 'products.custom_minimum_purchase_2', 'products.custom_minimum_purchase_3',
            'products.is_amazing as hasOffer','products.amazing_expire as expire' ,
            'products.amazing_off as offer_percentage','products.isReadyToSend','products.preparationTime')
        ->get();

        $res=array();

        foreach($cart as $item){
            switch ($item->role) {
                case 'Marketer':
                    switch ($item->grade) {
                        case "Main":
                            $price = $item->main_price_2;
                            $inventory=$item->main_inventory_2?$item->main_inventory_2:0;
                            $minimum_purchase=$item->main_minimum_purchase_2;

                            if($item->hasOffer==1 and $item->offer_percentage){
                                if($this->isValid($item->expire)){
                                    $off = $item->offer_percentage;
                                }else{
                                    $off = $item->main_off_2;
                                }
                            }else{
                                $off = $item->main_off_2;
                            }
                            break;

                        case "Custom":
                            $price = $item->custom_price_2;
                            $inventory=$item->custom_inventory_2?$item->custom_inventory_2:0;
                            $minimum_purchase=$item->custom_minimum_purchase_2;

                            if($item->hasOffer==1 and $item->offer_percentage){
                                if($this->isValid($item->expire)){
                                    $off = $item->offer_percentage;
                                }else{
                                    $off = $item->custom_off_2;
                                }
                            }else{
                                $off = $item->custom_off_2;
                            }
                            break;

                        case "Market":
                            $price = $item->market_price_2;
                            $inventory=$item->market_inventory_2?$item->market_inventory_2:0;
                            $minimum_purchase=$item->market_minimum_purchase_2;

                            if($item->hasOffer==1 and $item->offer_percentage){
                                if($this->isValid($item->expire)){
                                    $off = $item->offer_percentage;
                                }else{
                                    $off = $item->market_off_2;
                                }
                            }else{
                                $off = $item->market_off_2;
                            }
                            break;
                    }
                break;

                case 'Saler':
                    switch ($item->grade) {
                        case "Main":
                            $price = $item->main_price_3;
                            $inventory=$item->main_inventory_3?$item->main_inventory_3:0;
                            $minimum_purchase=$item->main_minimum_purchase_3;

                            if($item->hasOffer==1 and $item->offer_percentage){
                                if($this->isValid($item->expire)){
                                    $off = $item->offer_percentage;
                                }else{
                                    $off = $item->main_off_3;
                                }
                            }else{
                                $off = $item->main_off_3;
                            }
                            break;

                        case "Custom":
                            $price = $item->custom_price_3;
                            $inventory=$item->custom_inventory_3?$item->custom_inventory_3:0;
                            $minimum_purchase=$item->custom_minimum_purchase_3;

                            if($item->hasOffer==1 and $item->offer_percentage){
                                if($this->isValid($item->expire)){
                                    $off = $item->offer_percentage;
                                }else{
                                    $off = $item->custom_off_3;
                                }
                            }else{
                                $off = $item->custom_off_3;
                            }
                            break;

                        case "Market":
                            $price = $item->market_price_3;
                            $inventory=$item->market_inventory_3?$item->market_inventory_3:0;
                            $minimum_purchase=$item->market_minimum_purchase_3;

                            if($item->hasOffer==1 and $item->offer_percentage){
                                if($this->isValid($item->expire)){
                                    $off = $item->offer_percentage;
                                }else{
                                    $off = $item->market_off_3;
                                }
                            }else{
                                $off = $item->market_off_3;
                            }
                            break;
                    }
                break;

                default:
                    switch ($item->grade) {
                        case "Main":
                            $price = $item->main_price;
                            $inventory=$item->main_inventory?$item->main_inventory:0;
                            $minimum_purchase=$item->main_minimum_purchase;

                            if($item->hasOffer==1 and $item->offer_percentage){
                                if($this->isValid($item->expire)){
                                    $off = $item->offer_percentage;
                                }else{
                                    $off = $item->main_off;
                                }
                            }else{
                                $off = $item->main_off;
                            }
                            break;

                        case "Custom":
                            $price = $item->custom_price;
                            $inventory=$item->custom_inventory?$item->custom_inventory:0;
                            $minimum_purchase=$item->custom_minimum_purchase;

                            if($item->hasOffer==1 and $item->offer_percentage){
                                if($this->isValid($item->expire)){
                                    $off = $item->offer_percentage;
                                }else{
                                    $off = $item->custom_off;
                                }
                            }else{
                                $off = $item->custom_off;
                            }
                            break;

                        case "Market":
                            $price = $item->market_price;
                            $inventory=$item->market_inventory?$item->market_inventory:0;
                            $minimum_purchase=$item->market_minimum_purchase;

                            if($item->hasOffer==1 and $item->offer_percentage){
                                if($this->isValid($item->expire)){
                                    $off = $item->offer_percentage;
                                }else{
                                    $off = $item->market_off;
                                }
                            }else{
                                $off = $item->market_off;
                            }
                            break;
                    }
                break;
            }

            $cart_item=Cart::where("id",$item->cart_id)->first();

            if($cart_item){
                $cart_item->update([
                    'saved_price'=> $price,
                    'saved_off'=> $off
                ]);
            }

            $product=[
                'product_id'=> $item->product_id,
                'cart_id'=> $item->cart_id,
                'title'=> $item->title,
                'slug'=> $item->slug,
                'category'=> $item->category_name,
                'quantity'=> $item->quantity,
                'inventory'=> $inventory,
                'off'=> $off,
                'price'=> $price,
                'minimum_purchase'=> $minimum_purchase,
                'image'=> $item->image,
                'grade'=> $item->grade,
                'isPriceChanges'=> $item->isPriceChanges,
                'isReadyToSend'=> $item->isReadyToSend,
                'preparationTime'=> $item->preparationTime
            ];

            array_push($res,$product);
        }

        return $res;
    }

    public function shipping(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $this->check_cart_products_inventory($request->uuid);

        $cart=$this->getProductsCart($request->uuid);

        $summery = $this->getSummery($user->id,$request->uuid);

        $address = UsersAddress::where('user_id',$user->id)
        ->where('default',1)
        ->where('type',1)
        ->select('id','province_id','province','city_id','city', 'address', 'plaque', 'floor','building_unit', 'postal_code', 'default', 'type')
        ->first();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'products'=> $cart,
                'summery'=> $summery,
                'address'=> $address,
                'balance'=> $user->wallet_balance,
                'shipping'=> $this->getShippingMethods()
            ]
        ], Response::HTTP_OK);
    }

    public function payment(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $cart=$this->getProductsCart($request->uuid);

        $current_order = Orders::where("status",0)->where("user_id",$user->id)->first();

        if($current_order){
            $summery = $this->getTotalSummery($user->id,$request->uuid);

            $address = UsersAddress::where('user_id',$user->id)
            ->where('default',1)
            ->where('type',1)
            ->select('id','province_id','province','city_id','city', 'address', 'plaque', 'floor', 'postal_code', 'default', 'type')
            ->first();

            $current_order->update([
                'postal_code'=> $address->postal_code,
                'address'=> [
                    "province"=> $address->province,
                    "city"=> $address->city,
                    "address"=> $address->address,
                    "plaque"=> $address->plaque,
                    "floor"=> $address->floor,
                    "building_unit"=> $address->building_unit,
                    "postal_code"=> $address->postal_code
                ]
            ]);

            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'عملیات با موفقیت انجام شد.',
                'data' => [
                    'order_code'=> $current_order->order_code,
                    'products'=> $cart,
                    'summery'=> $summery,
                    'address'=> $address,
                    'balance'=> $user->wallet_balance,
                    'credit_purchase_inventory'=> $user->credit_purchase_inventory,
                    'isOfficial'=> $current_order->isOfficial,
                    'shipping'=> $this->getShippingMethods()
                ]
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => false,
            'statusCode' => 422,
            'message' => 'عملیات ناموفق بود.',
            'data' => null
        ], Response::HTTP_OK);
    }

    public function getShippingMethods(){
        $day=1;
        if(Carbon::now()->dayOfWeek==5){
            $day1=Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->addDay(1))->format('Y-m-d');
            $day=$day=2;
        }else{
            $day1=Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now())->format('Y-m-d');
            $day=$day+1;
        }

        if(Carbon::now()->addDay($day)->dayOfWeek==5){
           $day2=Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->addDay($day+1))->format('Y-m-d');
           $day=$day+2;
        }else{
            $day2=Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->addDay($day))->format('Y-m-d');
            $day=$day+1;
        }

        if(Carbon::now()->addDay($day)->dayOfWeek==5){
           $day3=Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->addDay($day+1))->format('Y-m-d');
           $day=$day+2;
        }else{
            $day3=Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->addDay($day))->format('Y-m-d');
            $day=$day+1;
        }

        if(Carbon::now()->addDay($day)->dayOfWeek==5){
           $day4=Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->addDay($day+1))->format('Y-m-d');
           $day=$day+2;
        }else{
            $day4=Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->addDay($day))->format('Y-m-d');
            $day=$day+1;
        }

        if(Carbon::now()->addDay($day)->dayOfWeek==5){
           $day5=Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->addDay($day+1))->format('Y-m-d');
           $day=$day+2;
        }else{
            $day5=Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->addDay($day))->format('Y-m-d');
            $day=$day+1;
        }

        if(Carbon::now()->addDay($day)->dayOfWeek==5){
           $day6=Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->addDay($day+1))->format('Y-m-d');
           $day=$day+2;
        }else{
            $day6=Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->addDay($day))->format('Y-m-d');
            $day=$day+1;
        }

        if(Carbon::now()->addDay($day)->dayOfWeek==5){
           $day7=Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->addDay($day+1))->format('Y-m-d');
           $day=$day+2;
        }else{
            $day7=Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->addDay($day))->format('Y-m-d');
            $day=$day+1;
        }

        $days=array(
            [
                "day"=> $day1,
                "isSelected"=> true
            ],
            [
                "day"=> $day2,
                "isSelected"=> false
            ],
            [
                "day"=> $day3,
                "isSelected"=> false
            ],
            [
                "day"=> $day4,
                "isSelected"=> false
            ],
            [
                "day"=> $day5,
                "isSelected"=> false
            ],
            [
                "day"=> $day6,
                "isSelected"=> false
            ],
            [
                "day"=> $day7,
                "isSelected"=> false
            ],
        );

        $res = ShippingMethods::where("status",1)->orderBy("order","DESC")->get();

        return [
            "methods"=> $res,
            "days"=> $days
        ];
    }

    public function remove_coupon(Request $request)
    {
        $response = explode(' ', $request->header('Authorization'));
        $token = trim($response[1]);
        $user = JWTAuth::authenticate($token);

        $current_order = Orders::where("status",0)->where("user_id",$user->id)->first();

        $current_order->update([
            'discount_code_id'=> null,
            'discounted_amount'=> null
        ]);

        $summery = $this->getTotalSummery($user->id,$request->uuid);

        return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => "کد تخفیف با موفقیت اعمال شد.",
                'data'=> $summery
            ], Response::HTTP_OK);
    }

    public function add_coupon(Request $request)
    {
        $response = explode(' ', $request->header('Authorization'));
        $token = trim($response[1]);
        $user = JWTAuth::authenticate($token);

        $credentials = $request->only('coupon');

        $validator = Validator::make($credentials, [
            'coupon' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'errors' => "کد تخفیف را وارد کنید."
            ], Response::HTTP_OK);
        }

        $res = $this->isValidateCoupon($request->coupon,$user);

        if($res == 0){
            $summery = $this->getTotalSummery($user->id,$request->uuid);

            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => "کد تخفیف با موفقیت اعمال شد.",
                'data'=> $summery
            ], Response::HTTP_OK);
        }

        return $this->coupon_response_message($res);

    }

    public function isValidateCoupon($code,$user)
    {
        $coupon=Discounts::where('code',$code)
        ->where("status",2)
        ->first();

        if(!$coupon){
            return 1;
        }

        $current_order = Orders::where("status",0)->where("user_id",$user->id)->first();

        if($coupon->type==1){
            $discounted_amount=$current_order->total*($coupon->value/100);
        }else{
            $discounted_amount=$coupon->value;
        }

        if($current_order->discount_code_id){
            return 4;
        }else{
            if($this->isValid($coupon->expire_date)==true){

                $all_orders=Orders::where("status",0)
                ->where("user_id",$user->id)
                ->where("discount_code_id",$coupon->id)
                ->get();

                if($coupon->number_use_limit == 0){
                    switch ($coupon->user_type) {
                        case 1:
                            $users = json_decode($coupon->user_limit, true);

                            for ($i=0; $i < count($users); $i++) {
                                if($users[$i]['id'] == $user->id){
                                    $current_order->update([
                                        'discount_code_id'=> $coupon->id,
                                        'discounted_amount'=> $discounted_amount
                                    ]);

                                    return 0;
                                }
                            }

                            break;

                        case 2:
                            $current_order->update([
                                'discount_code_id'=> $coupon->id,
                                'discounted_amount'=> $discounted_amount
                            ]);

                            return 0;
                            break;

                        case 3:
                            if($user->role == "Normal"){
                               $current_order->update([
                                    'discount_code_id'=> $coupon->id,
                                    'discounted_amount'=> $discounted_amount
                                ]);
                            }else{
                                return 2;
                            }
                            break;

                        case 4:
                            if($user->role == "Organization"){
                                $current_order->update([
                                    'discount_code_id'=> $coupon->id,
                                    'discounted_amount'=> $discounted_amount
                                ]);
                            }else{
                                return 2;
                            }
                            break;

                        case 5:
                            if($user->role == "Saler"){
                                $current_order->update([
                                    'discount_code_id'=> $coupon->id,
                                    'discounted_amount'=> $discounted_amount
                                ]);
                            }else{
                                return 2;
                            }
                            break;

                        case 6:
                            if($user->role == "Marketer"){
                                $current_order->update([
                                    'discount_code_id'=> $coupon->id,
                                    'discounted_amount'=> $discounted_amount
                                ]);
                            }else{
                                return 2;
                            }
                            break;
                    }
                }else{
                    if($all_orders->count()>=$coupon->number_use_limit){
                        return 5;
                    }else{
                        switch ($coupon->user_type) {
                            case 1:
                                $users = json_decode($coupon->user_limit, true);

                                for ($i=0; $i < count($users); $i++) {
                                    if($users[$i]['id'] == $user->id){
                                        $current_order->update([
                                            'discount_code_id'=> $coupon->id,
                                            'discounted_amount'=> $discounted_amount
                                        ]);

                                        return 0;
                                    }
                                }

                                break;

                            case 2:
                                $current_order->update([
                                    'discount_code_id'=> $coupon->id,
                                    'discounted_amount'=> $discounted_amount
                                ]);

                                return 0;
                                break;

                            case 3:
                                if($user->role == "Normal"){
                                   $current_order->update([
                                        'discount_code_id'=> $coupon->id,
                                        'discounted_amount'=> $discounted_amount
                                    ]);
                                }else{
                                    return 2;
                                }
                                break;

                            case 4:
                                if($user->role == "Organization"){
                                    $current_order->update([
                                        'discount_code_id'=> $coupon->id,
                                        'discounted_amount'=> $discounted_amount
                                    ]);
                                }else{
                                    return 2;
                                }
                                break;

                            case 5:
                                if($user->role == "Saler"){
                                    $current_order->update([
                                        'discount_code_id'=> $coupon->id,
                                        'discounted_amount'=> $discounted_amount
                                    ]);
                                }else{
                                    return 2;
                                }
                                break;

                            case 6:
                                if($user->role == "Marketer"){
                                    $current_order->update([
                                        'discount_code_id'=> $coupon->id,
                                        'discounted_amount'=> $discounted_amount
                                    ]);
                                }else{
                                    return 2;
                                }
                                break;
                        }
                    }
                }


            }else{
                return 3;
            }
        }
    }

    public function coupon_response_message($status){
        switch($status){
            case 0:
                return response()->json([
                    'success' => true,
                    'statusCode' => 201,
                    'message' => "کد تخفیف با موفقیت اعمال شد."
                ], Response::HTTP_OK);
            break;

            case 1:
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => "کد تخفیف وارد شده نامعتبر است."
                ], Response::HTTP_OK);
            break;

            case 2:
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => "شما مجاز به استفاده از این کد تخفیف نیستید."
                ], Response::HTTP_OK);
            break;


            case 3:
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => "کد تخفیف منقضی شده است."
                ], Response::HTTP_OK);
            break;

            case 4:
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => "تنها یک کد تخفیف روی هر خرید قابل استفاده است."
                ], Response::HTTP_OK);
            break;

            case 5:
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => "تعداد دفعات مجاز استفاده از این کد تخفیف به پایان رسیده است."
                ], Response::HTTP_OK);
            break;

            case 7:
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => "شما مجاز به استفاده از این کد تخفیف نیستید."
                ], Response::HTTP_OK);
            break;



            case 9:
                return response()->json([
                    'success' => true,
                    'statusCode' => 201,
                    'message' => "این کد تخفیف برای خرید کالاهای مشخص است."
                ], Response::HTTP_OK);
            break;

            default:
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'errors' => "عملیات ناموفق بود."
                ], Response::HTTP_OK);
            break;
        }
    }

    public function check_cart_products_inventory($uuid){
        $cart=Cart::leftJoin('products', function ($query) {
            $query->on('products.id', '=', 'carts.product_id');
        })
        ->where('carts.status',0)
        ->where('carts.uuid',$uuid)
        ->select('carts.id','carts.uuid','carts.status','carts.grade','carts.user_role as role',
            "products.main_inventory", "products.market_inventory", "products.custom_inventory","products.main_inventory_2","products.market_inventory_2",
            "products.custom_inventory_2","products.main_inventory_3", "products.market_inventory_3", "products.custom_inventory_3")
        ->get();

        foreach($cart as $item){
            switch ($item->role) {
                case 'Marketer':
                    switch ($item->grade) {
                        case "Main":
                            $inventory=$item->main_inventory_2?$item->main_inventory_2:0;
                            break;

                        case "Custom":
                            $inventory=$item->custom_inventory_2?$item->custom_inventory_2:0;
                            break;

                        case "Market":
                            $inventory=$item->market_inventory_2?$item->market_inventory_2:0;
                            break;
                    }
                break;

                case 'Saler':
                    switch ($item->grade) {
                        case "Main":
                            $inventory=$item->main_inventory_3?$item->main_inventory_3:0;
                            break;

                        case "Custom":
                            $inventory=$item->custom_inventory_3?$item->custom_inventory_3:0;
                            break;

                        case "Market":
                            $inventory=$item->market_inventory_3?$item->market_inventory_3:0;
                            break;
                    }
                break;

                default:
                    switch ($item->grade) {
                        case "Main":
                            $inventory=$item->main_inventory?$item->main_inventory:0;
                            break;

                        case "Custom":
                            $inventory=$item->custom_inventory?$item->custom_inventory:0;
                            break;

                        case "Market":
                            $inventory=$item->market_inventory?$item->market_inventory:0;
                            break;
                    }
                break;
            }

            if($inventory==0){
                Cart::where("id",$item->id)->delete();
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'quantity' => 'required|numeric',
            'uuid'=> 'required|string',
            'grade'=> 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $cartHasProduct = Cart::where("uuid",$request->uuid)
        ->where("status",0)->exists();

        if(!$cartHasProduct){
            $last_order = Orders::where("status",0)->where("user_id",$user->id)->first();
            if($last_order){
                $last_order->delete();
            }
        }else{
            $last_order = Orders::where("status",0)->where("user_id",$user->id)->first();
            if($last_order){
                $last_order->update([
                    "discount_code_id"=> null
                ]);
            }
        }

        $product = Product::select("main_inventory", "market_inventory", "custom_inventory","main_inventory_2",
        "market_inventory_2", "custom_inventory_2","main_inventory_3", "market_inventory_3", "custom_inventory_3",
        'main_price','main_price_2', 'main_price_3','custom_price','custom_price_2','custom_price_3','market_price','market_price_2','market_price_3',
        'main_off', 'market_off', 'custom_off','main_off_2', 'market_off_2', 'custom_off_2','main_off_3', 'market_off_3', 'custom_off_3',
        'products.main_minimum_purchase', 'products.main_minimum_purchase_2', 'products.main_minimum_purchase_3',
        'products.market_minimum_purchase', 'products.market_minimum_purchase_2', 'products.market_minimum_purchase_3',
        'products.custom_minimum_purchase', 'products.custom_minimum_purchase_2', 'products.custom_minimum_purchase_3',
        'is_amazing as hasOffer','amazing_expire as expire' ,'amazing_off as offer_percentage')
        ->where("id",$request->id)
        ->first();

        switch ($user->role) {
            case 'Marketer':
                switch ($request->grade) {
                    case "Main":
                        $price = $product->main_price_2;
                        $inventory=$product->main_inventory_2?$product->main_inventory_2:0;
                        $main_minimum_purchase=$product->main_minimum_purchase_2;

                        if($product->hasOffer==1 and $product->offer_percentage){
                            if($this->isValid($product->expire)){
                                $off = $product->offer_percentage;
                            }else{
                                $off = $product->main_off_2;
                            }
                        }else{
                            $off = $product->main_off_2;
                        }
                        break;

                    case "Custom":
                        $price = $product->custom_price_2;
                        $inventory=$product->custom_inventory_2?$product->custom_inventory_2:0;
                        $main_minimum_purchase=$product->custom_minimum_purchase_2;

                        if($product->hasOffer==1 and $product->offer_percentage){
                            if($this->isValid($product->expire)){
                                $off = $product->offer_percentage;
                            }else{
                                $off = $product->custom_off_2;
                            }
                        }else{
                            $off = $product->custom_off_2;
                        }
                        break;

                    case "Market":
                        $price = $product->market_price_2;
                        $inventory=$product->market_inventory_2?$product->market_inventory_2:0;
                        $main_minimum_purchase=$product->market_minimum_purchase_2;

                        if($product->hasOffer==1 and $product->offer_percentage){
                            if($this->isValid($product->expire)){
                                $off = $product->offer_percentage;
                            }else{
                                $off = $product->market_off_2;
                            }
                        }else{
                            $off = $product->market_off_2;
                        }
                        break;
                }
            break;

            case 'Saler':
                switch ($request->grade) {
                    case "Main":
                        $price = $product->main_price_3;
                        $inventory=$product->main_inventory_3?$product->main_inventory_3:0;
                        $main_minimum_purchase=$product->main_minimum_purchase_3;

                        if($product->hasOffer==1 and $product->offer_percentage){
                            if($this->isValid($product->expire)){
                                $off = $product->offer_percentage;
                            }else{
                                $off = $product->main_off_3;
                            }
                        }else{
                            $off = $product->main_off_3;
                        }
                        break;

                    case "Custom":
                        $price = $product->custom_price_3;
                        $inventory=$product->custom_inventory_3?$product->custom_inventory_3:0;
                        $main_minimum_purchase=$product->custom_minimum_purchase_3;

                        if($product->hasOffer==1 and $product->offer_percentage){
                            if($this->isValid($product->expire)){
                                $off = $product->offer_percentage;
                            }else{
                                $off = $product->custom_off_3;
                            }
                        }else{
                            $off = $product->custom_off_3;
                        }
                        break;

                    case "Market":
                        $price = $product->market_price_3;
                        $inventory=$product->market_inventory_3?$product->market_inventory_3:0;
                        $main_minimum_purchase=$product->market_minimum_purchase_3;

                        if($product->hasOffer==1 and $product->offer_percentage){
                            if($this->isValid($product->expire)){
                                $off = $product->offer_percentage;
                            }else{
                                $off = $product->market_off_3;
                            }
                        }else{
                            $off = $product->market_off_3;
                        }
                        break;
                }
            break;

            default:
                switch ($request->grade) {
                    case "Main":
                        $price = $product->main_price;
                        $inventory=$product->main_inventory?$product->main_inventory:0;
                        $main_minimum_purchase=$product->main_minimum_purchase;

                        if($product->hasOffer==1 and $product->offer_percentage){
                            if($this->isValid($product->expire)){
                                $off = $product->offer_percentage;
                            }else{
                                $off = $product->main_off;
                            }
                        }else{
                            $off = $product->main_off;
                        }
                        break;

                    case "Custom":
                        $price = $product->custom_price;
                        $inventory=$product->custom_inventory?$product->custom_inventory:0;
                        $main_minimum_purchase=$product->custom_minimum_purchase;

                        if($product->hasOffer==1 and $product->offer_percentage){
                            if($this->isValid($product->expire)){
                                $off = $product->offer_percentage;
                            }else{
                                $off = $product->custom_off;
                            }
                        }else{
                            $off = $product->custom_off;
                        }
                        break;

                    case "Market":
                        $price = $product->market_price;
                        $inventory=$product->market_inventory?$product->market_inventory:0;
                        $main_minimum_purchase=$product->market_minimum_purchase;

                        if($product->hasOffer==1 and $product->offer_percentage){
                            if($this->isValid($product->expire)){
                                $off = $product->offer_percentage;
                            }else{
                                $off = $product->market_off;
                            }
                        }else{
                            $off = $product->market_off;
                        }
                        break;
                }
            break;
        }

        if($main_minimum_purchase){
            if($request->quantity<$main_minimum_purchase){
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => [
                        'title'=> "حداقل تعداد سفارش ".$main_minimum_purchase." عدد می باشد."
                    ]
                ], Response::HTTP_OK);
            }
        }

        if($inventory<$request->quantity){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => [
                    'title'=> "موجودی این محصول کافی نیست،بعدا تلاش نمایید."
                ]
            ], Response::HTTP_OK);
        }

        $current_order = Orders::where("status",0)->where("user_id",$user->id);

        if($current_order->exists()){
            $order_id = $current_order->first()->id;
        }else{

            $last_order = Orders::latest()->first();
            if($last_order){
                $order_code = (int)$last_order->order_code + 1;
            }else{
              $order_code =  1001;
            }

            $shippingMethod = ShippingMethods::where("status",1)->first();

            if(Carbon::now()->dayOfWeek==5){
                $delivery_time=Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->addDay(1))->format('Y-m-d');
            }else{
                $delivery_time=Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now())->format('Y-m-d');
            }

            $order = Orders::create([
                'order_code'=> $order_code,
                'user_id'=> $user->id,
                'total'=> 0,
                'delivery_time'=> $delivery_time,
                'discount'=> 0,
                'sending_method'=> $shippingMethod->id,
                'sending_amount'=> $shippingMethod->price
            ]);

            $order_id = $order->id;
        }

        $cart=Cart::where("product_id",$request->id)
        ->where("uuid",$request->uuid)
        ->where("status",0)
        ->first();

        if($cart){
            $cart->increment('quantity',1);

            $products_cart = $this->getProductsCart($request->uuid);

            $summery = $this->getSummery($user->id,$request->uuid);

            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'عملیات با موفقیت انجام شد.',
                'data' => [
                    'product'=> [
                        'data'=>[
                            'id'=> $cart->id,
                            'quantity'=> $cart->quantity,
                            'grade_type'=> $cart->grade
                        ],
                        'action'=> "store"
                    ],
                    'products'=> $products_cart,
                    'summery'=> $summery,
                ]
            ], Response::HTTP_OK);
        }else{
            $cartNew = Cart::create([
                'product_id' => $request->id,
                'order_id' => $order_id,
                'quantity' => $request->quantity,
                'uuid'=> $request->uuid,
                'grade'=> $request->grade,
                'user_role'=> $user->role,
                'saved_price'=> $price,
                'saved_off'=> $off
            ]);

            $products_cart = $this->getProductsCart($request->uuid);

            $summery = $this->getSummery($user->id,$request->uuid);

            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'عملیات با موفقیت انجام شد.',
                'data' => [
                    'product'=> [
                        'data'=>[
                            'id'=> $cartNew->id,
                            'quantity'=> $cartNew->quantity,
                            'grade_type'=> $cartNew->grade
                        ],
                        'action'=> "store"
                    ],
                    'products'=> $products_cart,
                    'summery'=> $summery,
                ]
            ], Response::HTTP_OK);
        }
    }


    public function majorShopping(Request $request)
    {
        if(!$request->filled("items")){
            return response()->json([
                'success' => false,
                'statusCode' => 401,
                'message' => [
                    'error'=> 'محصولی به لیست اضافه نشده است.'
                ]
            ], Response::HTTP_OK);
        }

        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        if($user->role=="Normal"){
            return response()->json([
                'success' => false,
                'statusCode' => 401,
                'message' => [
                    'role'=> "برای خرید عمده محصولات، ابتدا حساب کاربری خود را ارتقاء دهید."
                ]
            ], Response::HTTP_OK);
        }

        $cartHasProduct = Cart::where("uuid",$request->uuid)
        ->where("status",0)->exists();

        if(!$cartHasProduct){
            $last_order = Orders::where("status",0)->where("user_id",$user->id)->first();
            if($last_order){
                $last_order->delete();
            }
        }else{
            $last_order = Orders::where("status",0)->where("user_id",$user->id)->first();
            if($last_order){
                $last_order->update([
                    "discount_code_id"=> null
                ]);
            }
        }

        foreach($request->items as $item)
        {
            $product = Product::select("main_inventory", "market_inventory", "custom_inventory","main_inventory_2",
                "market_inventory_2", "custom_inventory_2","main_inventory_3", "market_inventory_3", "custom_inventory_3",
                'main_price','main_price_2', 'main_price_3','custom_price','custom_price_2','custom_price_3','market_price','market_price_2','market_price_3',
                'main_off', 'market_off', 'custom_off','main_off_2', 'market_off_2', 'custom_off_2','main_off_3', 'market_off_3', 'custom_off_3',
                'products.main_minimum_purchase', 'products.main_minimum_purchase_2', 'products.main_minimum_purchase_3',
                'products.market_minimum_purchase', 'products.market_minimum_purchase_2', 'products.market_minimum_purchase_3',
                'products.custom_minimum_purchase', 'products.custom_minimum_purchase_2', 'products.custom_minimum_purchase_3',
                'is_amazing as hasOffer','amazing_expire as expire' ,'amazing_off as offer_percentage',"title")
                ->where("id",$item['id'])
                ->first();

            if($product)
            {
                switch ($user->role) {
                    case 'Marketer':
                        switch ($item['grade']) {
                            case "Main":
                                $price = $product->main_price_2;
                                $inventory=$product->main_inventory_2?$product->main_inventory_2:0;
                                $main_minimum_purchase=$product->main_minimum_purchase_2;

                                if($product->hasOffer==1 and $product->offer_percentage){
                                    if($this->isValid($product->expire)){
                                        $off = $product->offer_percentage;
                                    }else{
                                        $off = $product->main_off_2;
                                    }
                                }else{
                                    $off = $product->main_off_2;
                                }
                                break;

                            case "Custom":
                                $price = $product->custom_price_2;
                                $inventory=$product->custom_inventory_2?$product->custom_inventory_2:0;
                                $main_minimum_purchase=$product->custom_minimum_purchase_2;

                                if($product->hasOffer==1 and $product->offer_percentage){
                                    if($this->isValid($product->expire)){
                                        $off = $product->offer_percentage;
                                    }else{
                                        $off = $product->custom_off_2;
                                    }
                                }else{
                                    $off = $product->custom_off_2;
                                }
                                break;

                            case "Market":
                                $price = $product->market_price_2;
                                $inventory=$product->market_inventory_2?$product->market_inventory_2:0;
                                $main_minimum_purchase=$product->market_minimum_purchase_2;

                                if($product->hasOffer==1 and $product->offer_percentage){
                                    if($this->isValid($product->expire)){
                                        $off = $product->offer_percentage;
                                    }else{
                                        $off = $product->market_off_2;
                                    }
                                }else{
                                    $off = $product->market_off_2;
                                }
                                break;
                        }
                    break;

                    case 'Saler':
                        switch ($item['grade']) {
                            case "Main":
                                $price = $product->main_price_3;
                                $inventory=$product->main_inventory_3?$product->main_inventory_3:0;
                                $main_minimum_purchase=$product->main_minimum_purchase_3;

                                if($product->hasOffer==1 and $product->offer_percentage){
                                    if($this->isValid($product->expire)){
                                        $off = $product->offer_percentage;
                                    }else{
                                        $off = $product->main_off_3;
                                    }
                                }else{
                                    $off = $product->main_off_3;
                                }
                                break;

                            case "Custom":
                                $price = $product->custom_price_3;
                                $inventory=$product->custom_inventory_3?$product->custom_inventory_3:0;
                                $main_minimum_purchase=$product->custom_minimum_purchase_3;

                                if($product->hasOffer==1 and $product->offer_percentage){
                                    if($this->isValid($product->expire)){
                                        $off = $product->offer_percentage;
                                    }else{
                                        $off = $product->custom_off_3;
                                    }
                                }else{
                                    $off = $product->custom_off_3;
                                }
                                break;

                            case "Market":
                                $price = $product->market_price_3;
                                $inventory=$product->market_inventory_3?$product->market_inventory_3:0;
                                $main_minimum_purchase=$product->market_minimum_purchase_3;

                                if($product->hasOffer==1 and $product->offer_percentage){
                                    if($this->isValid($product->expire)){
                                        $off = $product->offer_percentage;
                                    }else{
                                        $off = $product->market_off_3;
                                    }
                                }else{
                                    $off = $product->market_off_3;
                                }
                                break;
                        }
                    break;

                    default:
                        switch ($item['grade']) {
                            case "Main":
                                $price = $product->main_price;
                                $inventory=$product->main_inventory?$product->main_inventory:0;
                                $main_minimum_purchase=$product->main_minimum_purchase;

                                if($product->hasOffer==1 and $product->offer_percentage){
                                    if($this->isValid($product->expire)){
                                        $off = $product->offer_percentage;
                                    }else{
                                        $off = $product->main_off;
                                    }
                                }else{
                                    $off = $product->main_off;
                                }
                                break;

                            case "Custom":
                                $price = $product->custom_price;
                                $inventory=$product->custom_inventory?$product->custom_inventory:0;
                                $main_minimum_purchase=$product->custom_minimum_purchase;

                                if($product->hasOffer==1 and $product->offer_percentage){
                                    if($this->isValid($product->expire)){
                                        $off = $product->offer_percentage;
                                    }else{
                                        $off = $product->custom_off;
                                    }
                                }else{
                                    $off = $product->custom_off;
                                }
                                break;

                            case "Market":
                                $price = $product->market_price;
                                $inventory=$product->market_inventory?$product->market_inventory:0;
                                $main_minimum_purchase=$product->market_minimum_purchase;

                                if($product->hasOffer==1 and $product->offer_percentage){
                                    if($this->isValid($product->expire)){
                                        $off = $product->offer_percentage;
                                    }else{
                                        $off = $product->market_off;
                                    }
                                }else{
                                    $off = $product->market_off;
                                }
                                break;
                        }
                    break;
                }

                if($main_minimum_purchase){
                    if($item['quantity']<$main_minimum_purchase){
                        return response()->json([
                            'success' => false,
                            'statusCode' => 422,
                            'message' => [
                                'title'=> "حداقل تعداد سفارش ".$main_minimum_purchase. " عدد برای محصول ".$product->title."  می باشد."
                            ]
                        ], Response::HTTP_OK);
                    }
                }

                if($inventory<$item['quantity']){
                    return response()->json([
                        'success' => false,
                        'statusCode' => 422,
                        'message' => [
                            'title'=>"موجودی این محصول کافی نیست."
                        ]
                    ], Response::HTTP_OK);
                }

                $current_order = Orders::where("status",0)->where("user_id",$user->id);

                if($current_order->exists()){
                    $order_id = $current_order->first()->id;
                }else{

                    $last_order = Orders::latest()->first();
                    if($last_order){
                        $order_code = (int)$last_order->order_code + 1;
                    }else{
                      $order_code =  1001;
                    }

                    $shippingMethod = ShippingMethods::where("status",1)->first();

                    if(Carbon::now()->dayOfWeek==5){
                        $delivery_time=Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now()->addDay(1))->format('Y-m-d');
                    }else{
                        $delivery_time=Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now())->format('Y-m-d');
                    }

                    $order = Orders::create([
                        'order_code'=> $order_code,
                        'user_id'=> $user->id,
                        'total'=> 0,
                        'delivery_time'=> $delivery_time,
                        'discount'=> 0,
                        'sending_method'=> $shippingMethod->id,
                        'sending_amount'=> $shippingMethod->price
                    ]);

                    $order_id = $order->id;
                }


                $cart=Cart::where("product_id",$item['id'])
                ->where("uuid",$request->uuid)
                ->where("status",0)
                ->first();

                if($cart){
                    $cart->increment('quantity',1);
                }else{
                    $cartNew = Cart::create([
                        'product_id' => $item['id'],
                        'order_id' => $order_id,
                        'quantity' => $item['quantity'],
                        'uuid'=> $request->uuid,
                        'grade'=> $item['grade'],
                        'user_role'=> $user->role,
                        'saved_price'=> $price,
                        'saved_off'=> $off
                    ]);
                }

            }

        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cart $cart)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|min:1',
            'grade'=> 'string',
            'uuid' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $product = Product::select("main_inventory", "market_inventory", "custom_inventory","main_inventory_2",
        "market_inventory_2", "custom_inventory_2","main_inventory_3", "market_inventory_3", "custom_inventory_3",
        'main_minimum_purchase', 'main_minimum_purchase_2', 'main_minimum_purchase_3',
        'market_minimum_purchase', 'market_minimum_purchase_2', 'market_minimum_purchase_3',
        'custom_minimum_purchase', 'custom_minimum_purchase_2', 'custom_minimum_purchase_3')
        ->where("id",$cart->product_id)
        ->first();

        switch ($user->role) {
            case 'Marketer':
                switch ($request->grade) {
                    case "Main":
                        $price = $product->main_price_2;
                        $inventory=$product->main_inventory_2?$product->main_inventory_2:0;
                        $main_minimum_purchase=$product->main_minimum_purchase_2;

                        if($product->hasOffer==1 and $product->offer_percentage){
                            if($this->isValid($product->expire)){
                                $off = $product->offer_percentage;
                            }else{
                                $off = $product->main_off_2;
                            }
                        }else{
                            $off = $product->main_off_2;
                        }
                        break;

                    case "Custom":
                        $price = $product->custom_price_2;
                        $inventory=$product->custom_inventory_2?$product->custom_inventory_2:0;
                        $main_minimum_purchase=$product->custom_minimum_purchase_2;

                        if($product->hasOffer==1 and $product->offer_percentage){
                            if($this->isValid($product->expire)){
                                $off = $product->offer_percentage;
                            }else{
                                $off = $product->custom_off_2;
                            }
                        }else{
                            $off = $product->custom_off_2;
                        }
                        break;

                    case "Market":
                        $price = $product->market_price_2;
                        $inventory=$product->market_inventory_2?$product->market_inventory_2:0;
                        $main_minimum_purchase=$product->market_minimum_purchase_2;

                        if($product->hasOffer==1 and $product->offer_percentage){
                            if($this->isValid($product->expire)){
                                $off = $product->offer_percentage;
                            }else{
                                $off = $product->market_off_2;
                            }
                        }else{
                            $off = $product->market_off_2;
                        }
                        break;
                }
            break;

            case 'Saler':
                switch ($request->grade) {
                    case "Main":
                        $price = $product->main_price_3;
                        $inventory=$product->main_inventory_3?$product->main_inventory_3:0;
                        $main_minimum_purchase=$product->main_minimum_purchase_3;

                        if($product->hasOffer==1 and $product->offer_percentage){
                            if($this->isValid($product->expire)){
                                $off = $product->offer_percentage;
                            }else{
                                $off = $product->main_off_3;
                            }
                        }else{
                            $off = $product->main_off_3;
                        }
                        break;

                    case "Custom":
                        $price = $product->custom_price_3;
                        $inventory=$product->custom_inventory_3?$product->custom_inventory_3:0;
                        $main_minimum_purchase=$product->custom_minimum_purchase_3;

                        if($product->hasOffer==1 and $product->offer_percentage){
                            if($this->isValid($product->expire)){
                                $off = $product->offer_percentage;
                            }else{
                                $off = $product->custom_off_3;
                            }
                        }else{
                            $off = $product->custom_off_3;
                        }
                        break;

                    case "Market":
                        $price = $product->market_price_3;
                        $inventory=$product->market_inventory_3?$product->market_inventory_3:0;
                        $main_minimum_purchase=$product->market_minimum_purchase_3;

                        if($product->hasOffer==1 and $product->offer_percentage){
                            if($this->isValid($product->expire)){
                                $off = $product->offer_percentage;
                            }else{
                                $off = $product->market_off_3;
                            }
                        }else{
                            $off = $product->market_off_3;
                        }
                        break;
                }
            break;

            default:
                switch ($request->grade) {
                    case "Main":
                        $price = $product->main_price;
                        $inventory=$product->main_inventory?$product->main_inventory:0;
                        $main_minimum_purchase=$product->main_minimum_purchase;

                        if($product->hasOffer==1 and $product->offer_percentage){
                            if($this->isValid($product->expire)){
                                $off = $product->offer_percentage;
                            }else{
                                $off = $product->main_off;
                            }
                        }else{
                            $off = $product->main_off;
                        }
                        break;

                    case "Custom":
                        $price = $product->custom_price;
                        $inventory=$product->custom_inventory?$product->custom_inventory:0;
                        $main_minimum_purchase=$product->custom_minimum_purchase;

                        if($product->hasOffer==1 and $product->offer_percentage){
                            if($this->isValid($product->expire)){
                                $off = $product->offer_percentage;
                            }else{
                                $off = $product->custom_off;
                            }
                        }else{
                            $off = $product->custom_off;
                        }
                        break;

                    case "Market":
                        $price = $product->market_price;
                        $inventory=$product->market_inventory?$product->market_inventory:0;
                        $main_minimum_purchase=$product->market_minimum_purchase;

                        if($product->hasOffer==1 and $product->offer_percentage){
                            if($this->isValid($product->expire)){
                                $off = $product->offer_percentage;
                            }else{
                                $off = $product->market_off;
                            }
                        }else{
                            $off = $product->market_off;
                        }
                        break;
                }
            break;
        }

        if($main_minimum_purchase){
            if($request->quantity<$main_minimum_purchase){
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => [
                        'title'=> "حداقل تعداد سفارش ".$main_minimum_purchase." عدد می باشد."
                    ]
                ], Response::HTTP_OK);
            }
        }

        if($inventory<$request->quantity){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => [
                    'title'=> "موجودی این محصول کافی نیست."
                ]
            ], Response::HTTP_OK);
        }

        if($request->has('grade')){
            $cart->update([
                "quantity" => $request->quantity,
                "grade"=> $request->grade
            ]);
        }else{
            $cart->update([
                "quantity" => $request->quantity,
                "grade"=> $cart->grade
            ]);
        }

        $products_cart = $this->getProductsCart($request->uuid);

        $summery = $this->getInitialsSummery($request->uuid);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'product'=> [
                    'data'=>[
                        'id'=> $cart->id,
                        'quantity'=> $cart->quantity,
                        'grade_type'=> $cart->grade
                    ],
                    'action'=> "update"
                ],
                'products'=> $products_cart,
                'summery'=> $summery,
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,Cart $cart)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        if($cart->status == 1){
            return response()->json([
                'success' => false,
                'statusCode' => 401,
                'message' => 'عملیات ناموفق بود.',
                'data' => $cart
            ], Response::HTTP_OK);
        }

        $cart->delete();

        $products_cart = $this->getProductsCart($request->uuid);

        if(count($products_cart)==0){
            Orders::where("status",0)->where("user_id",$user->id)->first()->delete();
        }

        $summery = $this->getInitialsSummery($request->uuid);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'product'=> [
                    'data'=> $cart->id,
                    'action'=> "destroy"
                ],
                'products'=> $products_cart,
                'summery'=> $summery,
            ]
        ], Response::HTTP_OK);
    }

    public function getInitialsSummery($uuid)
    {

        $order_items=Cart::where('carts.status',0)
            ->where('carts.uuid',$uuid)
            ->select('saved_off','saved_price','quantity')
            ->get();

        $sum=0;
        $discount=0;
        $total=0;

        foreach ($order_items as $item){
            $sum+=(int)$item->saved_price * $item->quantity;
            $discount+=((int)$item->saved_price*((int)$item->saved_off)/100) * $item->quantity;
        }

        $total = $sum - $discount;

        return [
            'sum'=> $sum,
            'profit'=> $discount,
            'final'=> $total
        ];
    }

    public function getSummery($user_id,$uuid)
    {
        $current_order = Orders::where("status",0)->where("user_id",$user_id)->first();

        if($current_order){
            $order_items=Cart::where('carts.status',0)
                ->where('carts.order_id',$current_order->id)
                ->select('saved_off','saved_price','quantity','product_id')
                ->get();

            $sum=0;
            $discount=0;
            $total=0;
            $isFree=false;
            $sending_amount=0;

            foreach ($order_items as $item){
                $sum+=(int)$item->saved_price * $item->quantity;
                $discount+=((int)$item->saved_price*((int)$item->saved_off)/100) * $item->quantity;

                if(count($order_items)==1){
                    $product=Product::select("isFreeDelivery","is_amazing","amazing_start","amazing_expire")->where("id",$item->product_id)->first();
                    if($product){
                        if((int)$product->isFreeDelivery==1 && $product->is_amazing==1){
                            $startTime = Carbon::parse($product->amazing_start);
                            $endTime = Carbon::parse($product->amazing_expire);
                            $now = Carbon::now();

                            if ($now->between($startTime, $endTime)) {
                                $isFree=true;
                            }
                        }
                    }
                }
            }

            $shippingMethods = ShippingMethods::select("id","price")->where("id",$current_order->sending_method)->first();

            if($isFree==true){
                $total = $sum - $discount;
            }else{
                $sending_amount=(int)$shippingMethods->price;
                $total = $sum - $discount+$sending_amount;
            }

            $current_order->update([
                'discount_code_id'=> null,
                'discounted_amount'=> null,
                'total'=> $total,
                'discount'=> $discount,
                'sending_amount'=> $isFree==true?0: $sending_amount
            ]);

            return [
                'sum'=> $sum,
                'profit'=> $discount,
                'final'=> $total,
                'delivery'=> [
                    'isFree'=> $isFree,
                    'type'=> $current_order->sending_method,
                    'amount'=> $sending_amount,
                    'day'=> $current_order->delivery_time
                ]
            ];
        }

        return [
            'order'=> $current_order,
            'sum'=> 0,
            'profit'=> 0,
            'final'=> 0,
            'delivery'=> [
                'isFree'=> false,
                'type'=> null,
                'amount'=> null
            ]
        ];
    }

    public function getTotalSummery($user_id,$uuid)
    {
        $current_order = Orders::where("status",0)->where("user_id",$user_id)->first();

        if($current_order){
            $discount_code = Discounts::select("code","type","value","expire_date")->where("id",$current_order->discount_code_id)->first();

            $order_items=Cart::where('carts.status',0)
                ->where('carts.order_id',$current_order->id)
                ->select('saved_off','saved_price','quantity','product_id')
                ->get();

            $sum=0;
            $discount=0;
            $coupon_price=0;
            $total=0;
            $final=0;
            $coupon="";
            $hasCoupon = false;
            $isFree=false;
            $sending_amount=0;

            foreach ($order_items as $item){
                $sum+=(int)$item->saved_price * $item->quantity;
                $discount+=((int)$item->saved_price*((int)$item->saved_off)/100) * $item->quantity;

                if(count($order_items)==1){
                    $product=Product::select("isFreeDelivery","is_amazing","amazing_start","amazing_expire")->where("id",$item->product_id)->first();
                    if($product){
                        if((int)$product->isFreeDelivery==1 && $product->is_amazing==1){
                            $startTime = Carbon::parse($product->amazing_start);
                            $endTime = Carbon::parse($product->amazing_expire);
                            $now = Carbon::now();

                            if ($now->between($startTime, $endTime)) {
                                $isFree=true;
                            }
                        }

                    }
                }
            }

            $shippingMethods = ShippingMethods::select("id","price")->where("id",$current_order->sending_method)->first();

            if($isFree==true){
                $total = $sum - $discount;
            }else{
                $sending_amount=$shippingMethods->price;
                $total = $sum - $discount + (int)$sending_amount;
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
                        'discount_code_id'=> null,
                        'discounted_amount'=> null
                    ]);
                }
            }else{
                $final=$total;
            }

            $current_order->update([
                'total'=> $final,
                'discount'=> $discount+$coupon_price,
                'sending_amount'=> $isFree==true?0: $sending_amount
            ]);

            return [
                'sum'=> $sum,
                'discount'=> $discount,
                'total'=> $total,
                'final'=> $final,
                'coupon'=>[
                    'exist'=> $hasCoupon,
                    'amount'=> $coupon_price,
                    'value'=> $coupon,
                ],
                'profit'=> $discount+$coupon_price,
                'delivery'=> [
                    'isFree'=> $isFree,
                    'type'=> $current_order->sending_method,
                    'amount'=> $sending_amount
                ]
            ];

        }

        return response()->json([
            'success' => false,
            'statusCode' => 422,
            'message' => 'عملیات ناموفق بود.',
            'data' => null
        ], Response::HTTP_OK);
    }

    public function set_delivery_type(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'type' => 'required|numeric',
            'uuid' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $current_order = Orders::where("status",0)->where("user_id",$user->id)->first();

        $shippingMethods = ShippingMethods::select("id","price")->where("id",$request->type)->first();

        $current_order->update([
            "sending_method"=> $shippingMethods->id,
            "sending_amount" => $shippingMethods->price
        ]);

        $order_items=Cart::where('carts.status',0)
        ->where('carts.order_id',$current_order->id)
        ->select('saved_off','saved_price','quantity','product_id')
        ->get();

        $sum=0;
        $discount=0;
        $final=0;
        $coupon="";
        $hasCoupon = false;
        $isFree=false;

        foreach ($order_items as $item){
            $sum+=(int)$item->saved_price * $item->quantity;
            $discount+=((int)$item->saved_price*((int)$item->saved_off)/100) * $item->quantity;

            if(count($order_items)==1){
                $product=Product::select("isFreeDelivery","is_amazing","amazing_start","amazing_expire")->where("id",$item->product_id)->first();
                if($product){
                    if((int)$product->isFreeDelivery==1 && $product->is_amazing==1){
                        $isFree=true;
                        $startTime = Carbon::parse($product->amazing_start);
                        $endTime = Carbon::parse($product->amazing_expire);
                        $now = Carbon::now();

                        if ($now->between($startTime, $endTime)) {
                            $isFree=true;
                        }
                    }

                }
            }
        }

        if($isFree==true){
            $final = $sum - $discount;
        }else{
            $final = $sum - $discount + $current_order->sending_amount;
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'sum'=> $sum,
                'final'=> $final,
                'profit'=> $discount,
                'delivery'=> [
                    'isFree'=> $isFree,
                    'type'=> $current_order->sending_method,
                    'amount'=> $current_order->sending_amount
                ]
            ]
        ], Response::HTTP_OK);

    }

    public function set_order_delivery_time(Request $request){
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'day' => 'required|string',
            'uuid' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $current_order = Orders::where("status",0)->where("user_id",$user->id)->first();

        if($current_order){
            $current_order->update([
                "delivery_time"=> $request->day,
            ]);
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.'
        ], Response::HTTP_OK);
    }

    public function request_official_invoice(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'uuid' => 'required',
            'isOfficial'=> 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $current_order = Orders::where("status",0)->where("user_id",$user->id)->first();

        $current_order->update([
            'isOfficial'=> $request->isOfficial
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.'
        ], Response::HTTP_OK);

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

}
