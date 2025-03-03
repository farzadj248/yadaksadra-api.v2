<?php

namespace App\Http\Controllers;

use App\Helper\EventLogs;

use App\Models\Product;
use App\Models\Admin;
use App\Models\ProductCarTypes;
use App\Models\ProductCarYears;
use App\Helper\GenerateSlug;
use App\Http\Resources\CommonResources;
use App\Models\ProductDefinedCar;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class productCarYearController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->page){
            $data=ProductCarYears::where("model_id",$request->id)
                ->where('title', 'like', '%' . $request->q . '%')->paginate(10);
            return CommonResources::collection($data);
        }else{
            $ProductCarYears=ProductCarYears::where("model_id",$request->id)->get();
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $ProductCarYears
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
            'title' => 'required|string|max:255',
            'model_id'=> 'required|numeric',
            'model_name'=> 'required|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $productCarYear = ProductCarYears::create([
            'title' => $request->title,
            'model_id' => $request->model_id,
            'model_name' => $request->model_name,
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "افزودن سال ساخت برای خودروها",
            	    'body' => $productCarYear,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productCarYear
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductCarYears $productCarYear)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'model_id'=> 'required|numeric',
            'model_name'=> 'required|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $productCarYear->update([
            'title' => $request->title,
            'model_id' => $request->model_id,
            'model_name' => $request->model_name
        ]);

        ProductDefinedCar::where("year_id",$productCarYear->id)->update([
            "year_name"=> $request->title
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "ویرایش سال ساخت خودرو",
            	    'body' => $productCarYear,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productCarYear
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,ProductCarYears $productCarYear)
    {
        $productCarYear->delete();

        ProductDefinedCar::where("year_id",$productCarYear->id)->delete();

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "حذف سال ساخت خودرو",
            	    'body' => $productCarYear,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productCarYear
        ], Response::HTTP_OK);
    }
}
