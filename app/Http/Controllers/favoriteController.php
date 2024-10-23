<?php

namespace App\Http\Controllers;

use App\Models\Favorites;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class favoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $favorites=Favorites::leftJoin('products', function ($query) {
            $query->on('products.id', '=', 'favorites.product_id');
        })
        ->leftJoin('products_images', function ($query) {
            $query->on('products_images.product_id', '=', 'favorites.product_id')
                ->whereRaw('products_images.id IN (select MAX(a2.id) from products_images as a2 join products as u2 on u2.id = a2.product_id group by u2.id)');
        })
        ->where('favorites.uuid',$request->uuid);

        switch($request->role){
            case "Marketer":
                $favorites->select('favorites.id','favorites.product_id','products.title','products.slug',
                    'products.main_price_2','products.main_off_2','products.main_inventory_2',
                    'products.custom_price_2','products.custom_off_2','custom_inventory_2',
                    'products.market_price_2','products.market_off_2','products.market_inventory_2',
                    'products.brand_name','products.status', 'products_images.url as image');

                    $products = $favorites->paginate(10);
                    $products->setCollection(
                        $products->getCollection()
                       ->map(function($item) use ($request){
                        if((int)$item->main_inventory_2>0){
                            $item['price']=$item->main_price_2;
                            $item['off']=$item->main_off_2;
                            $item['inventory']=$item->main_inventory_2;
                            $item['grade']="Main";
                        }else if((int)$item->custom_inventory_2>0){
                            $item['price']=$item->custom_price_2;
                            $item['off']=$item->custom_off_2;
                            $item['inventory']=$item->custom_inventory_2;
                            $item['grade']="Custom";
                        }else if((int)$item->market_inventory_2>0){
                            $item['price']=$item->market_price_2;
                            $item['off']=$item->market_off_2;
                            $item['inventory']=$item->market_inventory_2;
                            $item['grade']="Market";
                        }else{
                            $item['price']=0;
                            $item['off']=0;
                            $item['inventory']=0;
                            $item['grade']="Main";
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

                        return $item;
                    })
                    );
                break;

            case "Saler":
                $favorites->select('favorites.id','favorites.product_id','products.title','products.slug',
                    'products.main_price_3','products.main_off_3','products.main_inventory_3',
                    'products.custom_price_3','products.custom_off_3','custom_inventory_3',
                    'products.market_price_3','products.market_off_3','products.market_inventory_3',
                    'products.brand_name','products.status', 'products_images.url as image');

                    $products = $favorites->paginate(10);
                    $products->setCollection(
                        $products->getCollection()
                        ->map(function($item) use ($request){
                            if((int)$item->main_inventory_3>0){
                                $item['price']=$item->main_price_3;
                                $item['off']=$item->main_off_3;
                                $item['inventory']=$item->main_inventory_3;
                                $item['grade']="Main";
                            }else if((int)$item->custom_inventory_3>0){
                                $item['price']=$item->custom_price_3;
                                $item['off']=$item->custom_off_3;
                                $item['inventory']=$item->custom_inventory_3;
                                $item['grade']="Custom";
                            }else if((int)$item->market_inventory_3>0){
                                $item['price']=$item->market_price_3;
                                $item['off']=$item->market_off_3;
                                $item['inventory']=$item->market_inventory_3;
                                $item['grade']="Market";
                            }else{
                                $item['price']=0;
                                $item['off']=0;
                                $item['inventory']=0;
                                $item['grade']="Main";
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

                            return $item;
                        })
                    );
                break;

            default :
                $favorites->select('favorites.id','favorites.product_id','products.title','products.slug',
                    'products.main_price','products.main_off','products.main_inventory',
                    'products.custom_price','products.custom_off','custom_inventory',
                    'products.market_price','products.market_off','products.market_inventory',
                    'products.brand_name','products.status', 'products_images.url as image');

                    $products = $favorites->paginate(10);
                    $products->setCollection(
                        $products->getCollection()
                        ->map(function($item) use ($request){
                            if((int)$item->main_inventory>0){
                                $item['price']=$item->main_price;
                                $item['off']=$item->main_off;
                                $item['inventory']=$item->main_inventory;
                                $item['grade']="Main";
                            }else if((int)$item->custom_inventory>0){
                                $item['price']=$item->custom_price;
                                $item['off']=$item->custom_off;
                                $item['inventory']=$item->custom_inventory;
                                $item['grade']="Custom";
                            }else if((int)$item->market_inventory>0){
                                $item['price']=$item->market_price;
                                $item['off']=$item->market_off;
                                $item['inventory']=$item->market_inventory;
                                $item['grade']="Market";
                            }else{
                                $item['price']=0;
                                $item['off']=0;
                                $item['inventory']=0;
                                $item['grade']="Main";
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

                            return $item;
                        })
                    );
                break;
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $products
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'uuid'=> 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $favorites=Favorites::where("product_id",$request->id)
        ->where("uuid",$request->uuid);

        if($favorites->exists()){
            $fav = $favorites->first();
            $fav->delete();

            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'عملیات با موفقیت انجام شد.',
                'data' => $fav
            ], Response::HTTP_OK);
        }

        $favoritesNew = Favorites::create([
            'product_id' => $request->id,
            'uuid'=> $request->uuid
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $favoritesNew
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,Favorites $favorite)
    {
        if($favorite->uuid == $request->uuid){
            $favorite->delete();

            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'عملیات با موفقیت انجام شد.',
                'data' => $favorite
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => false,
            'statusCode' => 422,
            'message' => 'عملیات ناموفق بود.'
        ], Response::HTTP_OK);
    }
}
