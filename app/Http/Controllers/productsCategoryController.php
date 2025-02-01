<?php

namespace App\Http\Controllers;

use App\Helper\EventLogs;

use App\Models\Admin;
use App\Models\Product;
use App\Helper\GenerateSlug;
use App\Models\ProductCarCompany;
use App\Models\ProductsCategories;
use App\Models\ProductsBrands;
use App\Models\ProductCarTypes;
use App\Models\ProductCountryBuilders;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class productsCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
       
        if($request->page){
            $productsCategories=ProductsCategories::where('title', 'like', '%' . $request->q . '%')
            ->where("parent_id",$request->parent)->paginate(10);

        }else{
            $productsCategories=ProductsCategories::where("parent_id",$request->parent)->get();
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productsCategories
        ], Response::HTTP_OK);
    }

    public function getCategories(Request $request)
    {
        if($request->category_parent!=0){
            $res=ProductsCategories::select("id","title")->where("title",$request->category_parent)->first();
            if($res){
                $category_parent=$res->id;
            }else{
                $category_parent=0;
            }
        }else{
            $category_parent=0;
        }

        if($request->brand_parent!=0){
            $res=ProductsBrands::select("id","title")->where("title",$request->brand_parent)->first();
            if($res){
                $brand_parent=$res->id;
            }else{
                $brand_parent=0;
            }
        }else{
            $brand_parent=0;
        }

        $categories = ProductsCategories::where("parent_id",$category_parent)->orderBy("order","ASC")->get();
        $brands = ProductsBrands::orderBy("order","asc")->where("parent_id",$brand_parent)->get();
        $companies = ProductCarCompany::orderBy("order","asc")->get();
        $country_builders = ProductCountryBuilders::orderBy("order","asc")->get();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'categories'=> $categories,
                'brands'=> $brands,
                'companies'=> $companies,
                'country_builders'=> $country_builders,
            ]
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
            'title' => 'required|string|max:255|unique:products_categories',
            'order' => 'required|numeric',
            'image_url' => 'nullable|string',
            'parent_id' => 'nullable|numeric'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $category = ProductsCategories::select("title","parent_id")
        ->where("title",$request->title)
        ->where("parent_id",$request->parent_id)
        ->exists();

        if($category==true){
            return response()->json([
                'success' => false,
                'statusCode' => 201,
                'message' => [
                    'title'=> 'دسته بندی با این عنوان قبلأ ثبت شده است.',
                ]
            ], Response::HTTP_OK);
        }

        if($request->image_url){
            $img = $request->image_url;
        }else{
           $img = "https://dl.yadaksadra.com/storage/images/not-icon.svg";
        }

        $productsCateory = ProductsCategories::create([
            'title' => $request->title,
            'order' => $request->order,
            'image_url'=> $img,
            'parent_id'=> $request->parent_id
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "ایجاد دسته بندی جدید برای محصولات",
            	    'body' => $productsCateory,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productsCateory
        ], Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductsCategories $productsCateory)
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
                'message' => $validator->errors()->toJson()
            ], Response::HTTP_OK);
        }

        if($request->title != $productsCateory->title){

            $category = ProductsCategories::select("title")
            ->where("title",$request->title)
            ->where("parent_id",$request->parent_id)
            ->exists();

            if($category==true){
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => [
                        'title'=> 'دسته بندی با این عنوان قبلأ ثبت شده است.',
                    ]
                ], Response::HTTP_OK);
            }
        }

        if($request->image_url){
            $img = $request->image_url;
        }else{
           $img = "https://dl.yadaksadra.com/storage/images/not-icon.svg";
        }

        $productsCateory->update([
            'title' => $request->title,
            'order' => $request->order,
            'image_url'=> $img,
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "ویرایش دسته بندی محصولات",
            	    'body' => $productsCateory,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productsCateory
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,ProductsCategories $productsCateory)
    {
        if(Product::where("category_id",$productsCateory->id)->exists() or ProductsCategories::where("parent_id",$productsCateory->id)->exists()){
            return response()->json([
                'success' => false,
                'statusCode' => 201,
                'message' => 'به دلیل وابستگی با سایر بخش ها امکان حذف وجود ندارد.'
            ], Response::HTTP_OK);
        }

        $productsCateory->delete();

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "حذف دسته بندی محصولات",
            	    'body' => $productsCateory,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productsCateory
        ], Response::HTTP_OK);
    }
}
