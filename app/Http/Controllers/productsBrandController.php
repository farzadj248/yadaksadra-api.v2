<?php

namespace App\Http\Controllers;

use App\Helper\EventLogs;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductsBrands;
use App\Helper\GenerateSlug;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class productsBrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->page){
            $res=ProductsBrands::where("parent_id",$request->parent);

            if($request->q){
                $res->whereRaw('concat(products_brands.title,products_brands.fa_title) like ?', "%{$request->q}%");
            }

            $ProductsBrands = $res->paginate(10);

        }else{
            $ProductsBrands=ProductsBrands::where("parent_id",$request->parent)->get();
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $ProductsBrands
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
            'title' => 'required|string|max:255|unique:products_brands',
            'fa_title' => 'required|string|max:255',
            'order' => 'required|numeric',
            'image_url' => 'nullable|string',
            'parent_id' => 'numeric'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if($request->image_url){
            $img = $request->image_url;
        }else{
           $img = "https://dl.yadaksadra.com/storage/images/not-icon.svg";
        }

        $productsBrand = ProductsBrands::create([
            'title' => $request->title,
            'fa_title'=> $request->fa_title,
            'parent_id'=> $request->parent_id,
            'order' => $request->order,
            'image_url' => $img
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "افزودن برند برای محصولات",
            	    'body' => $productsBrand,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productsBrand
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductsBrands $productsBrand)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'fa_title' => 'required|string|max:255',
            'order' => 'required|numeric',
            'image_url' => 'nullable|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if($request->title != $productsBrand->title){
            if(ProductsBrands::where("title",$request->title)->exists()){
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => [
                        "title"=>"برند با این عنوان قبلأ ثبت شده است."
                    ],
                ], Response::HTTP_OK);
            }
        }

        if($request->image_url){
            $img = $request->image_url;
        }else{
           $img = "https://dl.yadaksadra.com/storage/images/not-icon.svg";
        }

        $productsBrand->update([
            'title' => $request->title,
            'fa_title'=> $request->fa_title,
            'order' => $request->order,
            'image_url' => $img
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "ویرایش برند محصولات",
            	    'body' => $productsBrand,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productsBrand
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,ProductsBrands $productsBrand)
    {
        if(Product::where("brand_id",$productsBrand->id)->exists() or ProductsBrands::where("parent_id",$productsBrand->id)->exists()){
            return response()->json([
                'success' => false,
                'statusCode' => 201,
                'message' => 'به دلیل وابستگی با سایر بخش ها امکان حذف وجود ندارد.'
            ], Response::HTTP_OK);
        }

        $productsBrand->delete();

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "حذف برند محصولات",
            	    'body' => $productsBrand,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productsBrand
        ], Response::HTTP_OK);
    }
}
