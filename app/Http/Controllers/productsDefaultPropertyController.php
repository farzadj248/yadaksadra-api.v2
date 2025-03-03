<?php

namespace App\Http\Controllers;

use App\Helper\EventLogs;
use App\Http\Resources\CommonResources;
use App\Models\Admin;
use App\Models\ProductsProperties;
use App\Models\ProductsDefaultProperty;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class productsDefaultPropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->page){
            $data=ProductsDefaultProperty::where('title', 'like', '%' . $request->q . '%')
            ->where("parent_id",$request->parent_id)->paginate(10);
            return CommonResources::collection($data);
        }else{
            $data=ProductsDefaultProperty::where("parent_id",$request->parent_id)->get();
            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'عملیات با موفقیت انجام شد.',
                'data' => $data
            ], Response::HTTP_OK);
        }
    }

    public function getAll(Request $request)
    {
        $properties=ProductsDefaultProperty::where('parent_id',0)
        ->select("id","title")
        ->get();

        $properties=$properties->map(function($item){
            $item->childs=ProductsDefaultProperty::where('parent_id',$item->id)
            ->select("id","value")
            ->get();
            return $item;
        });

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $properties
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
        if($request->parent_id==0){
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255|unique:products_default_properties',
                'value' => 'nullable|string',
                'parent_id' => 'nullable|numeric'
            ]);
        }else{
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'value' => 'nullable|string',
                'parent_id' => 'nullable|numeric'
            ]);
        }

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $properties = ProductsDefaultProperty::create([
            'title' => $request->title,
            'value' => $request->value,
            'parent_id'=> $request->parent_id
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "افزودن ویژگی جدید برای محصولات",
            	    'body' => $properties,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $properties
        ], Response::HTTP_OK);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductsDefaultProperty $productsDefaultProperty)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'value' => 'nullable|string',
            'parent' => 'nullable|numeric'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $productsDefaultProperty->update([
            'title' => $request->title,
            'value' => $request->value,
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "ویرایش ویژگی محصولات",
            	    'body' => $productsDefaultProperty,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

         return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productsDefaultProperty
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,ProductsDefaultProperty $productsDefaultProperty)
    {
        $productsDefaultProperty->delete();

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "حذف ویژگی محصولات",
            	    'body' => $productsDefaultProperty,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productsDefaultProperty
        ], Response::HTTP_OK);
    }
}
