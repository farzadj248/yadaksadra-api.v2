<?php

namespace App\Http\Controllers;

use App\Helper\EventLogs;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductCountryBuilders;
use App\Helper\GenerateSlug;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class ProductCountryBuildersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->page){
            $ProductCountryBuilders=ProductCountryBuilders::where('title', 'like', '%' . $request->q . '%')->paginate(10);
        }else{
            $ProductCountryBuilders=ProductCountryBuilders::all();
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $ProductCountryBuilders
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
            'title' => 'required|string|max:255|unique:product_country_builders',
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

        if($request->image_url){
            $img = $request->image_url;
        }else{
           $img = "https://dl.yadaksadra.com/storage/images/not-icon.svg";
        }

        $productCountryBuilders = ProductCountryBuilders::create([
            'title' => $request->title,
            'order' => $request->order,
            'image_url' => $img
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "ایجاد کشور سازنده برای محصولات",
            	    'body' => $productCountryBuilders,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productCountryBuilders
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductCountryBuilders $productCountryBuilder)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
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

        if($request->title != $productCountryBuilder->title){
            if(ProductCountryBuilders::where("title",$request->title)->exists()){
                return response()->json([
                    'success' => false,
                    'statusCode' => 201,
                    'message' => [
                        "title"=>"شرکت سازنده با این عنوان قبلأ ثبت شده است."
                    ],
                ], Response::HTTP_OK);
            }
        }

        if($request->image_url){
            $img = $request->image_url;
        }else{
           $img = "https://dl.yadaksadra.com/storage/images/not-icon.svg";
        }

        $productCountryBuilder->update([
            'title' => $request->title,
            'order' => $request->order,
            'image_url' => $img
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "ویرایش کشور سازنده محصولات",
            	    'body' => $productCountryBuilder,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productCountryBuilder
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,ProductCountryBuilders $productCountryBuilders)
    {
        if(Product::where("country_id",$productCountryBuilders->id)->exists()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' =>  [
                    'title'=> 'به دلیل وابستگی با سایر بخش ها امکان حذف وجود ندارد.'
                 ],
            ], Response::HTTP_OK);
        }

        $productCountryBuilders->delete();

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "حذف کشور سازنده محصولات",
            	    'body' => $productCountryBuilders,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productCountryBuilders
        ], Response::HTTP_OK);
    }
}
