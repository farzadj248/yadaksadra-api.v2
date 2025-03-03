<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ProductCarCompany;
use App\Models\ProductsBrands;
use App\Models\ShopInfo;
use App\Models\Favorites;
use App\Models\Cart;
use App\Models\User;
use App\Models\Banners;
use App\Models\Orders;
use App\Models\Transactions;
use App\Models\Product;
use App\Models\SocialNetwork;
use App\Models\Ticket;
use \Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    public function adminHome(Request $request)
    {
        $users = User::select("id", "first_name", "last_name", "avatar", "role")
            ->orderBy("id", "desc")
            ->take(10)->get();

        $users_count = User::count();

        $orders = Orders::leftJoin('users', function ($query) {
            $query->on('users.id', '=', 'orders.user_id');
        })
            ->select('orders.*', 'users.first_name', 'users.last_name')
            ->orderBy("orders.id", "desc")
            ->Where("orders.status", '!=', 0)
            ->paginate(10);

        $products = Product::select(
            'products.id',
            'products.title',
            'products.slug',
            'products.main_inventory as inventory',
            'products.status',
            'products_images.url as image',
            'products.main_price as price',
            'products.brand_name',
            'products.main_off as off',
            'products.rating',
            'products.views'
        )
            ->leftJoin('products_images', function ($query) {
                $query->on('products_images.product_id', '=', 'products.id')
                    ->whereRaw('products_images.id IN (select MAX(a2.id) from products_images as a2 join products as u2 on u2.id = a2.product_id group by u2.id)');
            })
            ->orderBy("id", "desc")
            ->where("products.main_inventory", "<", 10)
            ->paginate(10);

        $orders_awating = Orders::WhereIn("status", [1])
            ->count();

        $orders_confirmed = Orders::WhereIn("status", [2, 3, 4, 5])
            ->count();

        $orders_cancelled = Orders::WhereIn("status", [6])
            ->count();

        $transactions = Transactions::leftJoin('users', function ($query) {
            $query->on('users.id', '=', 'transactions.user_id');
        })
            ->select('transactions.*', 'users.first_name', 'users.last_name')
            ->orderBy("transactions.id", "desc")
            ->take(10)->get();

        $products_count = Product::count();

        $article_count = Article::count();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                "users" => $users,
                "order_count" => [
                    "awating" => $orders_awating,
                    "confirmed" => $orders_confirmed,
                    "cancelled" => $orders_cancelled
                ],
                "users_count" => $users_count,
                "products_count" => $products_count,
                "orders" => $orders,
                "article_count" => $article_count,
                "transactions" => $transactions,
                "products" => $products
            ]
        ], Response::HTTP_OK);
    }

    public function shopHistory()
    {
        $tickets = Ticket::count();
        $orders = Orders::count();
        $users = User::count();
        $products = Product::count();

        return response()->json([
            'success' => true,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                "tickets" => $tickets,
                "orders" => $orders,
                "users" => $users,
                "products" => $products
            ]
        ], Response::HTTP_OK);
    }

    public function webHome(Request $request)
    {

        $data = Cache::remember('web_home', 5, function () use ($request) {
            $role = "Normal";
            if ($request->header('Authorization')) {
                $response1 = explode(' ', $request->header('Authorization'));
                if ($response1[1] && $response1[1] != "undefined") {
                    $token = trim($response1[1]);
                    // $user =JWTAuth::authenticate($token);
                    // dd(JWTAuth::authenticate($token),$user);
                    $user = JWTAuth::setToken($token)->toUser();
                    // dd($user);
                    if ($user) {
                        $role = $user->role;
                    }
                }
            }
            // $role="Marketer";  
            $mare_sale_products = Product::leftJoin('products_images', function ($query) {
                $query->on('products_images.product_id', '=', 'products.id')
                    ->whereRaw('products_images.id IN (select MAX(a2.id) from products_images as a2 join products as u2 on u2.id = a2.product_id group by u2.id)');
            })
                ->orderBy("products.number_sales", "asc")
                ->take(10);

            switch ($role) {
                case "Marketer":
                    $mare_sale_products->select(
                        'products.id',
                        'products.title',
                        'products.slug',
                        "products.isFreeDelivery",
                        'products.main_price_2',
                        'products.main_off_2',
                        'products.main_inventory_2',
                        'products.custom_price_2',
                        'products.custom_off_2',
                        'custom_inventory_2',
                        'products.market_price_2',
                        'products.market_off_2',
                        'products.market_inventory_2',
                        'products.brand_name',
                        'products.status',
                        'products_images.url as image',
                        'products.rating',
                        'products.views',
                        "is_amazing",
                        "amazing_expire",
                        "amazing_start",
                        "amazing_off"
                    );

                    $mare_sale_products = $mare_sale_products->get()
                        ->map(function ($item) use ($request) {
                            $item['isFavorite'] = Favorites::where("product_id", $item->id)
                                ->where('uuid', $request->uuid)->exists();

                            if ((int)$item->main_inventory_2 > 0) {
                                $item['price'] = $item->main_price_2;
                                $item['off'] = $item->main_off_2;
                                $item['inventory'] = $item->main_inventory_2;
                                $item['grade'] = "Main";
                            } else if ((int)$item->custom_inventory_2 > 0) {
                                $item['price'] = $item->custom_price_2;
                                $item['off'] = $item->custom_off_2;
                                $item['inventory'] = $item->custom_inventory_2;
                                $item['grade'] = "Custom";
                            } else if ((int)$item->market_inventory_2 > 0) {
                                $item['price'] = $item->market_price_2;
                                $item['off'] = $item->market_off_2;
                                $item['inventory'] = $item->market_inventory_2;
                                $item['grade'] = "Market";
                            } else {
                                $item['price'] = 0;
                                $item['off'] = 0;
                                $item['inventory'] = 0;
                                $item['grade'] = "Main";
                            }

                            if ($item->is_amazing == 1) {
                                $startTime = Carbon::parse($item->amazing_start);
                                $endTime = Carbon::parse($item->amazing_expire);
                                $now = Carbon::now();

                                if ($now->between($startTime, $endTime)) {
                                    $item['isOffer'] = true;
                                    $item['offer'] = $item->amazing_off;
                                } else {
                                    $item['isOffer'] = false;
                                }
                            } else {
                                $item['isOffer'] = false;
                            }

                            unset($item['amazing_start']);
                            unset($item['amazing_expire']);
                            unset($item['is_amazing']);
                            unset($item['amazing_off']);

                            unset($item['main_inventory_2']);
                            unset($item['main_price_2']);
                            unset($item['main_off_2']);

                            unset($item['custom_inventory_2']);
                            unset($item['custom_price_2']);
                            unset($item['custom_off_2']);

                            unset($item['market_inventory_2']);
                            unset($item['market_price_2']);
                            unset($item['market_off_2']);

                            return $item;
                        });
                    break;

                case "Saler":
                    $mare_sale_products->select(
                        'products.id',
                        'products.title',
                        'products.slug',
                        "products.isFreeDelivery",
                        'products.main_price_3',
                        'products.main_off_3',
                        'products.main_inventory_3',
                        'products.custom_price_3',
                        'products.custom_off_3',
                        'custom_inventory_3',
                        'products.market_price_3',
                        'products.market_off_3',
                        'products.market_inventory_3',
                        'products.brand_name',
                        'products.status',
                        'products_images.url as image',
                        'products.rating',
                        'products.views',
                        "is_amazing",
                        "amazing_expire",
                        "amazing_start",
                        "amazing_off"
                    );

                    $mare_sale_products = $mare_sale_products->get()
                        ->map(function ($item) use ($request) {
                            $item['isFavorite'] = Favorites::where("product_id", $item->id)
                                ->where('uuid', $request->uuid)->exists();

                            if ((int)$item->main_inventory_3 > 0) {
                                $item['price'] = $item->main_price_3;
                                $item['off'] = $item->main_off_3;
                                $item['inventory'] = $item->main_inventory_3;
                                $item['grade'] = "Main";
                            } else if ((int)$item->custom_inventory_3 > 0) {
                                $item['price'] = $item->custom_price_3;
                                $item['off'] = $item->custom_off_3;
                                $item['inventory'] = $item->custom_inventory_3;
                                $item['grade'] = "Custom";
                            } else if ((int)$item->market_inventory_3 > 0) {
                                $item['price'] = $item->market_price_3;
                                $item['off'] = $item->market_off_3;
                                $item['inventory'] = $item->market_inventory_3;
                                $item['grade'] = "Market";
                            } else {
                                $item['price'] = 0;
                                $item['off'] = 0;
                                $item['inventory'] = 0;
                                $item['grade'] = "Main";
                            }

                            if ($item->is_amazing == 1) {
                                $startTime = Carbon::parse($item->amazing_start);
                                $endTime = Carbon::parse($item->amazing_expire);
                                $now = Carbon::now();

                                if ($now->between($startTime, $endTime)) {
                                    $item['isOffer'] = true;
                                    $item['offer'] = $item->amazing_off;
                                } else {
                                    $item['isOffer'] = false;
                                }
                            } else {
                                $item['isOffer'] = false;
                            }

                            unset($item['amazing_start']);
                            unset($item['amazing_expire']);
                            unset($item['is_amazing']);
                            unset($item['amazing_off']);

                            unset($item['main_inventory_3']);
                            unset($item['main_price_3']);
                            unset($item['main_off_3']);

                            unset($item['custom_inventory_3']);
                            unset($item['custom_price_3']);
                            unset($item['custom_off_3']);

                            unset($item['market_inventory_3']);
                            unset($item['market_price_3']);
                            unset($item['market_off_3']);

                            return $item;
                        });
                    break;

                default:
                    $mare_sale_products->select(
                        'products.id',
                        'products.title',
                        'products.slug',
                        "products.isFreeDelivery",
                        'products.main_price',
                        'products.main_off',
                        'products.main_inventory',
                        'products.custom_price',
                        'products.custom_off',
                        'custom_inventory',
                        'products.market_price',
                        'products.market_off',
                        'products.market_inventory',
                        'products.brand_name',
                        'products.status',
                        'products_images.url as image',
                        'products.rating',
                        'products.views',
                        "is_amazing",
                        "amazing_expire",
                        "amazing_start",
                        "amazing_off"
                    );

                    $mare_sale_products = $mare_sale_products->get()
                        ->map(function ($item) use ($request) {
                            $item['isFavorite'] = Favorites::where("product_id", $item->id)
                                ->where('uuid', $request->uuid)->exists();

                            if ((int)$item->main_inventory > 0) {
                                $item['price'] = $item->main_price;
                                $item['off'] = $item->main_off;
                                $item['inventory'] = $item->main_inventory;
                                $item['grade'] = "Main";
                            } else if ((int)$item->custom_inventory > 0) {
                                $item['price'] = $item->custom_price;
                                $item['off'] = $item->custom_off;
                                $item['inventory'] = $item->custom_inventory;
                                $item['grade'] = "Custom";
                            } else if ((int)$item->market_inventory > 0) {
                                $item['price'] = $item->market_price;
                                $item['off'] = $item->market_off;
                                $item['inventory'] = $item->market_inventory;
                                $item['grade'] = "Market";
                            } else {
                                $item['price'] = 0;
                                $item['off'] = 0;
                                $item['inventory'] = 0;
                            }

                            if ($item->is_amazing == 1) {
                                $startTime = Carbon::parse($item->amazing_start);
                                $endTime = Carbon::parse($item->amazing_expire);
                                $now = Carbon::now();

                                if ($now->between($startTime, $endTime)) {
                                    $item['isOffer'] = true;
                                    $item['offer'] = $item->amazing_off;
                                } else {
                                    $item['isOffer'] = false;
                                }
                            } else {
                                $item['isOffer'] = false;
                            }

                            unset($item['amazing_start']);
                            unset($item['amazing_expire']);
                            unset($item['is_amazing']);
                            unset($item['amazing_off']);

                            unset($item['main_inventory']);
                            unset($item['main_price']);
                            unset($item['main_off']);

                            unset($item['custom_inventory']);
                            unset($item['custom_price']);
                            unset($item['custom_off']);

                            unset($item['market_inventory']);
                            unset($item['market_price']);
                            unset($item['market_off']);

                            return $item;
                        });
                    break;
            }
            $currentDate  = Carbon::now();
            $amazin_products =  Product::leftJoin('products_images', function ($query) {
                $query->on('products_images.product_id', '=', 'products.id')
                    ->whereRaw('products_images.id IN (select MAX(a2.id) from products_images as a2 join products as u2 on u2.id = a2.product_id group by u2.id)');
            })
                ->orderBy("products.updated_at", 'asc')
                ->where("products.is_amazing", "=", 1);


                $discounts =  Product::leftJoin('products_images', function ($query) {
                    $query->on('products_images.product_id', '=', 'products.id')
                        ->whereRaw('products_images.id IN (select MAX(a2.id) from products_images as a2 join products as u2 on u2.id = a2.product_id group by u2.id)');
                })
                    ->orderBy("products.updated_at", 'asc')
                    ->where('products.special_offer', true); 

            // $amazin_products = Product::with('image') // Eager load the 'image' relationship
            //     ->where('is_amazing', 1) // Filter products that are amazing
            //     ->orderBy('updated_at', 'asc') // Order by the updated_at column
            //     ;
            switch ($role) {
                case "Marketer":
                    $amazin_products->select(
                        'products.id',
                        'products.title',
                        'products.slug',
                        "products.isFreeDelivery",
                        'products.main_price_2',
                        'products.main_off_2',
                        'products.main_inventory_2',
                        'products.custom_price_2',
                        'products.custom_off_2',
                        'custom_inventory_2',
                        'products.market_price_2',
                        'products.market_off_2',
                        'products.market_inventory_2',
                        'products.status',
                        'products_images.url as image',
                        'products.brand_name',
                        'products.rating',
                        'products.views',
                        'products.amazing_expire as expire',
                        'products.amazing_off as offer_percentage',
                        'products.is_amazing as hasOffer',
                        'products.amazing_start',
                        'products.updated_at'
                    );

                    $amazin_products = $amazin_products->get()
                        ->map(function ($item) use ($request, $currentDate) {
                            $item['isFavorite'] = Favorites::where("product_id", $item->id)
                                ->where('uuid', $request->uuid)->exists();
                              

                            if ((int)$item->main_inventory_2 > 0) {
                                $item['price'] = $item->main_price_2;
                                $item['off'] = $item->main_off_2;
                                $item['inventory'] = $item->main_inventory_2;
                                $item['grade'] = "Main";
                            } else if ((int)$item->custom_inventory_2 > 0) {
                                $item['price'] = $item->custom_price_2;
                                $item['off'] = $item->custom_off_2;
                                $item['inventory'] = $item->custom_inventory_2;
                                $item['grade'] = "Custom";
                            } else if ((int)$item->market_inventory_2 > 0) {
                                $item['price'] = $item->market_price_2;
                                $item['off'] = $item->market_off_2;
                                $item['inventory'] = $item->market_inventory_2;
                                $item['grade'] = "Market";
                            } else {
                                $item['price'] = 0;
                                $item['off'] = 0;
                                $item['inventory'] = 0;
                                $item['grade'] = "Main";
                            }

                            unset($item['main_inventory_2']);
                            unset($item['main_price_2']);
                            unset($item['main_off_2']);

                            unset($item['custom_inventory_2']);
                            unset($item['custom_price_2']);
                            unset($item['custom_off_2']);

                            unset($item['market_inventory_2']);
                            unset($item['market_price_2']);
                            unset($item['market_off_2']);


                            $startTime = Carbon::parse($item->amazing_start);
                            $endTime = Carbon::parse($item->expire);
                            $now = Carbon::now();

                            if ($now->between($startTime, $endTime)) {
                                return $item;
                            }
                        });

                    $sliders = Banners::where('status', 1)
                        ->orderBy("order", "asc")
                        ->where("type", 1)
                        ->where("user_type", 5)
                        ->orWhere("user_type", 1)
                        ->select("title","text","image_url", "image_link","type")
                        ->get();

                    break;

                case "Saler":
                    $amazin_products->select(
                        'products.id',
                        'products.title',
                        'products.slug',
                        "products.isFreeDelivery",
                        'products.main_price_3',
                        'products.main_off_3',
                        'products.main_inventory_3',
                        'products.custom_price_3',
                        'products.custom_off_3',
                        'custom_inventory_3',
                        'products.market_price_3',
                        'products.market_off_3',
                        'products.market_inventory_3',
                        'products.brand_name',
                        'products.status',
                        'products_images.url as image',
                        'products.rating',
                        'products.views',
                        'products.amazing_expire as expire',
                        'products.amazing_off as offer_percentage',
                        'products.is_amazing as hasOffer',
                        'products.amazing_start',
                        'products.updated_at'
                    );

                    $amazin_products = $amazin_products->get()
                        ->map(function ($item) use ($request, $currentDate) {
                            $item['isFavorite'] = Favorites::where("product_id", $item->id)
                                ->where('uuid', $request->uuid)->exists();
                       

                            if ((int)$item->main_inventory_3 > 0) {
                                $item['price'] = $item->main_price_3;
                                $item['off'] = $item->main_off_3;
                                $item['inventory'] = $item->main_inventory_3;
                                $item['grade'] = "Main";
                            } else if ((int)$item->custom_inventory_3 > 0) {
                                $item['price'] = $item->custom_price_3;
                                $item['off'] = $item->custom_off_3;
                                $item['inventory'] = $item->custom_inventory_3;
                                $item['grade'] = "Custom";
                            } else if ((int)$item->market_inventory_3 > 0) {
                                $item['price'] = $item->market_price_3;
                                $item['off'] = $item->market_off_3;
                                $item['inventory'] = $item->market_inventory_3;
                                $item['grade'] = "Market";
                            } else {
                                $item['price'] = 0;
                                $item['off'] = 0;
                                $item['inventory'] = 0;
                                $item['grade'] = "Main";
                            }

                            unset($item['main_inventory_3']);
                            unset($item['main_price_3']);
                            unset($item['main_off_3']);

                            unset($item['custom_inventory_3']);
                            unset($item['custom_price_3']);
                            unset($item['custom_off_3']);

                            unset($item['market_inventory_3']);
                            unset($item['market_price_3']);
                            unset($item['market_off_3']);

                            $startTime = Carbon::parse($item->amazing_start);
                            $endTime = Carbon::parse($item->expire);
                            $now = Carbon::now();

                            if ($now->between($startTime, $endTime)) {
                                return $item;
                            }
                        });

                    $sliders = Banners::where('status', 1)
                        ->orderBy("order", "asc")
                        ->where("type", 1)
                        ->where("user_type", 4)
                        ->orWhere("user_type", 1)
                        ->select("title","text","image_url", "image_link","type")
                        ->get();
                    break;

                default:
                    $amazin_products->select(
                        'products.id',
                        'products.title',
                        'products.slug',
                        "products.isFreeDelivery",
                        'products.main_price',
                        'products.main_off',
                        'products.main_inventory',
                        'products.custom_price',
                        'products.custom_off',
                        'custom_inventory',
                        'products.market_price',
                        'products.market_off',
                        'products.market_inventory',
                        'products.brand_name',
                        'products.status',
                        'products_images.url as image',
                        'products.rating',
                        'products.views',
                        'products.amazing_expire as expire',
                        'products.amazing_off as offer_percentage',
                        'products.is_amazing as hasOffer',
                        'products.amazing_start',
                        'products.updated_at'
                    );

                    $amazin_products = $amazin_products->get()
                        ->map(function ($item) use ($request, $currentDate) {
                            $item['isFavorite'] = Favorites::where("product_id", $item->id)
                                ->where('uuid', $request->uuid)->exists();


                            if ((int)$item->main_inventory > 0) {
                                $item['price'] = $item->main_price;
                                $item['off'] = $item->main_off;
                                $item['inventory'] = $item->main_inventory;
                                $item['grade'] = "Main";
                            } else if ((int)$item->custom_inventory > 0) {
                                $item['price'] = $item->custom_price;
                                $item['off'] = $item->custom_off;
                                $item['inventory'] = $item->custom_inventory;
                                $item['grade'] = "Custom";
                            } else if ((int)$item->market_inventory > 0) {
                                $item['price'] = $item->market_price;
                                $item['off'] = $item->market_off;
                                $item['inventory'] = $item->market_inventory;
                                $item['grade'] = "Market";
                            } else {
                                $item['price'] = 0;
                                $item['off'] = 0;
                                $item['inventory'] = 0;
                                $item['grade'] = "Main";
                            }

                            unset($item['main_inventory']);
                            unset($item['main_price']);
                            unset($item['main_off']);

                            unset($item['custom_inventory']);
                            unset($item['custom_price']);
                            unset($item['custom_off']);

                            unset($item['market_inventory']);
                            unset($item['market_price']);
                            unset($item['market_off']);

                            $startTime = Carbon::parse($item->amazing_start);
                            $endTime = Carbon::parse($item->expire);
                            $now = Carbon::now();

                            if ($now->between($startTime, $endTime)) {
                                return $item;
                            }
                        });

                    $sliders = Banners::where('status', 1)
                        ->orderBy("order", "asc")
                        ->where("type", 1)
                        ->where("user_type", 1)
                        ->orWhere("user_type", 1)
                        ->select("title", "text","image_url", "image_link","type")
                        ->get();
                    break;
            }

            switch ($role) {
                case "Marketer":
                    $discounts->select(
                        'products.id',
                        'products.title',
                        'products.slug',
                        "products.isFreeDelivery",
                        'products.main_price_2',
                        'products.main_off_2',
                        'products.main_inventory_2',
                        'products.custom_price_2',
                        'products.custom_off_2',
                        'custom_inventory_2',
                        'products.market_price_2',
                        'products.market_off_2',
                        'products.market_inventory_2',
                        'products.status',
                        'products_images.url as image',
                        'products.brand_name',
                        'products.rating',
                        'products.views',
                        'products.amazing_expire as expire',
                        'products.amazing_off as offer_percentage',
                        'products.is_amazing as hasOffer',
                        'products.amazing_start',
                        'products.updated_at'
                    );
        
                    $discounts = $discounts->get()
                        ->map(function ($item) use ($request, $currentDate) {
                            $item['isFavorite'] = Favorites::where("product_id", $item->id)
                                ->where('uuid', $request->uuid)->exists();
                              
        
                            if ((int)$item->main_inventory_2 > 0) {
                                $item['price'] = $item->main_price_2;
                                $item['off'] = $item->main_off_2;
                                $item['inventory'] = $item->main_inventory_2;
                                $item['grade'] = "Main";
                            } else if ((int)$item->custom_inventory_2 > 0) {
                                $item['price'] = $item->custom_price_2;
                                $item['off'] = $item->custom_off_2;
                                $item['inventory'] = $item->custom_inventory_2;
                                $item['grade'] = "Custom";
                            } else if ((int)$item->market_inventory_2 > 0) {
                                $item['price'] = $item->market_price_2;
                                $item['off'] = $item->market_off_2;
                                $item['inventory'] = $item->market_inventory_2;
                                $item['grade'] = "Market";
                            } else {
                                $item['price'] = 0;
                                $item['off'] = 0;
                                $item['inventory'] = 0;
                                $item['grade'] = "Main";
                            }
        
                            unset($item['main_inventory_2']);
                            unset($item['main_price_2']);
                            unset($item['main_off_2']);
        
                            unset($item['custom_inventory_2']);
                            unset($item['custom_price_2']);
                            unset($item['custom_off_2']);
        
                            unset($item['market_inventory_2']);
                            unset($item['market_price_2']);
                            unset($item['market_off_2']);
        
        
                            $startTime = Carbon::parse($item->amazing_start);
                            $endTime = Carbon::parse($item->expire);
                            $now = Carbon::now();
        
                            if ($now->between($startTime, $endTime)) {
                                return $item;
                            }
                        });
        
                    $sliders = Banners::where('status', 1)
                        ->orderBy("order", "asc")
                        ->where("type", 1)
                        ->where("user_type", 5)
                        ->orWhere("user_type", 1)
                        ->select("title", "text","image_url", "image_link","type")
                        ->get();
        
                    break;
        
                case "Saler":
                    $discounts->select(
                        'products.id',
                        'products.title',
                        'products.slug',
                        "products.isFreeDelivery",
                        'products.main_price_3',
                        'products.main_off_3',
                        'products.main_inventory_3',
                        'products.custom_price_3',
                        'products.custom_off_3',
                        'custom_inventory_3',
                        'products.market_price_3',
                        'products.market_off_3',
                        'products.market_inventory_3',
                        'products.brand_name',
                        'products.status',
                        'products_images.url as image',
                        'products.rating',
                        'products.views',
                        'products.amazing_expire as expire',
                        'products.amazing_off as offer_percentage',
                        'products.is_amazing as hasOffer',
                        'products.amazing_start',
                        'products.updated_at'
                    );
        
                    $discounts = $discounts->get()
                        ->map(function ($item) use ($request, $currentDate) {
                            $item['isFavorite'] = Favorites::where("product_id", $item->id)
                                ->where('uuid', $request->uuid)->exists();
                       
        
                            if ((int)$item->main_inventory_3 > 0) {
                                $item['price'] = $item->main_price_3;
                                $item['off'] = $item->main_off_3;
                                $item['inventory'] = $item->main_inventory_3;
                                $item['grade'] = "Main";
                            } else if ((int)$item->custom_inventory_3 > 0) {
                                $item['price'] = $item->custom_price_3;
                                $item['off'] = $item->custom_off_3;
                                $item['inventory'] = $item->custom_inventory_3;
                                $item['grade'] = "Custom";
                            } else if ((int)$item->market_inventory_3 > 0) {
                                $item['price'] = $item->market_price_3;
                                $item['off'] = $item->market_off_3;
                                $item['inventory'] = $item->market_inventory_3;
                                $item['grade'] = "Market";
                            } else {
                                $item['price'] = 0;
                                $item['off'] = 0;
                                $item['inventory'] = 0;
                                $item['grade'] = "Main";
                            }
        
                            unset($item['main_inventory_3']);
                            unset($item['main_price_3']);
                            unset($item['main_off_3']);
        
                            unset($item['custom_inventory_3']);
                            unset($item['custom_price_3']);
                            unset($item['custom_off_3']);
        
                            unset($item['market_inventory_3']);
                            unset($item['market_price_3']);
                            unset($item['market_off_3']);
        
                            $startTime = Carbon::parse($item->amazing_start);
                            $endTime = Carbon::parse($item->expire);
                            $now = Carbon::now();
        
                            if ($now->between($startTime, $endTime)) {
                                return $item;
                            }
                        });
        
                    $sliders = Banners::where('status', 1)
                        ->orderBy("order", "asc")
                        ->where("type", 1)
                        ->where("user_type", 4)
                        ->orWhere("user_type", 1)
                        ->select("title", "text","image_url", "image_link","type")
                        ->get();
                    break;
        
                default:
                    $discounts->select(
                        'products.id',
                        'products.title',
                        'products.slug',
                        "products.isFreeDelivery",
                        'products.main_price',
                        'products.main_off',
                        'products.main_inventory',
                        'products.custom_price',
                        'products.custom_off',
                        'custom_inventory',
                        'products.market_price',
                        'products.market_off',
                        'products.market_inventory',
                        'products.brand_name',
                        'products.status',
                        'products_images.url as image',
                        'products.rating',
                        'products.views',
                        'products.amazing_expire as expire',
                        'products.amazing_off as offer_percentage',
                        'products.is_amazing as hasOffer',
                        'products.amazing_start',
                        'products.updated_at'
                    );
        
                    $discounts = $discounts->get()
                        ->map(function ($item) use ($request, $currentDate) {
                            $item['isFavorite'] = Favorites::where("product_id", $item->id)
                                ->where('uuid', $request->uuid)->exists();
        
        
                            if ((int)$item->main_inventory > 0) {
                                $item['price'] = $item->main_price;
                                $item['off'] = $item->main_off;
                                $item['inventory'] = $item->main_inventory;
                                $item['grade'] = "Main";
                            } else if ((int)$item->custom_inventory > 0) {
                                $item['price'] = $item->custom_price;
                                $item['off'] = $item->custom_off;
                                $item['inventory'] = $item->custom_inventory;
                                $item['grade'] = "Custom";
                            } else if ((int)$item->market_inventory > 0) {
                                $item['price'] = $item->market_price;
                                $item['off'] = $item->market_off;
                                $item['inventory'] = $item->market_inventory;
                                $item['grade'] = "Market";
                            } else {
                                $item['price'] = 0;
                                $item['off'] = 0;
                                $item['inventory'] = 0;
                                $item['grade'] = "Main";
                            }
        
                            unset($item['main_inventory']);
                            unset($item['main_price']);
                            unset($item['main_off']);
        
                            unset($item['custom_inventory']);
                            unset($item['custom_price']);
                            unset($item['custom_off']);
        
                            unset($item['market_inventory']);
                            unset($item['market_price']);
                            unset($item['market_off']);
        
                            $startTime = Carbon::parse($item->amazing_start);
                            $endTime = Carbon::parse($item->expire);
                            $now = Carbon::now();
        
                            if ($now->between($startTime, $endTime)) {
                                return $item;
                            }
                        });
        
                    $sliders = Banners::where('status', 1)
                        ->orderBy("order", "asc")
                        ->where("type", 1)
                        ->where("user_type", 1)
                        ->orWhere("user_type", 1)
                        ->select("title","text" ,"image_url", "image_link","type")
                        ->get();
                    break;
            }
          
       

            $collection = new Collection($amazin_products);
            $amazin_products = $collection->filter(function ($value, $key) {
                return !empty($value);
            });
        



            $brands = ProductsBrands::where("parent_id", 0)->select("title", "image_url")->take(10)->get();

            $companies = ProductCarCompany::orderBy("order", "asc")
                ->select("id", "title", "en_title", "image_url")
                ->get();
             
                foreach($amazin_products as $amazin_product){
                    $amazin_product->image=json_decode($amazin_product->image);

                }
                foreach($discounts as $discount){
                    $discount->image=json_decode($discount->image);

                }


            $articles = Article::select('id', 'slug', 'title', 'short_body', 'comments_number', 'image_url', 'created_at')->get();

            return [
                "sliders" => $sliders,
                "brands" => $brands,
                "companies" => $companies,
                "products" => [
                    "more_sales" => $mare_sale_products,
                    "amazing" => $amazin_products,
                    "discount" =>$discounts
                ],
                // "articles"=>  $articles,
            ];
        });


        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $data
        ], Response::HTTP_OK);
    }

    public function shop_info(Request $request)
    {
        $shopInfo = ShopInfo::first();

        $cart = Cart::where("uuid", $request->uuid)
            ->where("status", 1)
            ->count();

        $favorites = Favorites::where("uuid", $request->uuid)
            ->count();
        $socialNetworks =  SocialNetwork::all();


        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                "contactInfo" => $shopInfo,
                "counters" => [
                    "cart" => $cart,
                    "favorite" => $favorites,
                    "socialNetwork" => $socialNetworks,
                ],
            ]
        ], Response::HTTP_OK);
    }

    public function saleStatistics(Request $request)
    {
        $year = $request->year;
        $orders = Transactions::select('amount', 'created_at')
            ->orderBy('created_at')
            ->whereIn("status", [2])
            ->whereIn("type", [1, 2, 3]);

        $res3 = $orders->whereYear('created_at', $year)
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('m');
            });

        $orders = [];
        $ordersCount = [];

        foreach ($res3 as $key => $value) {
            $total = 0;
            foreach ($value as $item) {
                $total += $item->amount;
            }
            $orders[(int)$key] = $total;
        }

        for ($i = 1; $i <= 12; $i++) {
            if (!empty($orders[$i])) {
                $ordersCount[$i] = $orders[$i];
            } else {
                $ordersCount[$i] = 0;
            }
        }

        $years = [];
        for ($i = 2023; $i <= (int)Carbon::now()->format('Y'); $i++) {
            array_push($years, $i);
        }

        $total_year = Transactions::whereIn("status", [2])
            ->whereIn("type", [1, 2, 3])
            ->whereYear('created_at', $request->year)
            ->sum('amount');


        $total_month = Transactions::whereIn("status", [2])
            ->whereIn("type", [1, 2, 3])
            ->whereYear('created_at', Carbon::now()->format('Y'))
            ->whereMonth('created_at', Carbon::now()->format('m'))
            ->sum('amount');

        $total_week = Transactions::whereBetween('created_at', [date('Y-m-d', strtotime('-8 days')), date('Y-m-d', strtotime('+1 days'))])
            ->whereIn("status", [1, 2])
            ->whereIn("type", [1, 2, 3])
            ->sum('amount');

        $total_day = Transactions::whereIn("status", [2])
            ->whereIn("type", [1, 2, 3])
            ->whereDate('created_at', Carbon::now()->format('Y-m-d'))
            ->sum('amount');


        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                "total" => [
                    "graph" => array_values($ordersCount),
                    "years" => $years,
                ],
                "year" => $total_year,
                "month" => $total_month,
                "week" => $total_week,
                "day" => $total_day
            ]
        ], Response::HTTP_OK);
    }

    public function isValid($expire_date)
    {
        $now = Carbon::now();
        $created_at = $now->toDateTimeString();

        $date1 = Carbon::createFromFormat('Y-m-d H:i:s', $expire_date);
        $date2 = Carbon::createFromFormat('Y-m-d H:i:s', $created_at);

        if ($date1->eq($date2)) {
            return true;
        } else {
            if ($date1->gt($date2)) {
                return true;
            } else {
                return false;
            }
        }
    }
}
