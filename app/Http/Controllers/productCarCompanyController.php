<?php

namespace App\Http\Controllers;

use App\Helper\EventLogs;
use App\Http\Resources\CommonResources;
use App\Models\Admin;
use App\Models\ProductCarCompany;
use App\Models\ProductDefinedCar;
use App\Models\ProductCarTypes;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class productCarCompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->page){
            if($request->q){
                $data=ProductCarCompany::whereRaw('concat(ProductCarCompany.title,ProductCarCompany.en_title) like ?', "%{$request->q}%")->paginate(10);
            }else{
                $data = ProductCarCompany::paginate(10);
            }
            return CommonResources::collection($data);
        }else{
            $companies=ProductCarCompany::all();
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $companies
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
            'en_title'=> 'nullable|string',
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

        $company = ProductCarCompany::create([
            'title' => $request->title,
            'order' => $request->order,
            'en_title'=> $request->en_title,
            'image_url' => $img,
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "افزودن شرکت خودروسازی جدید",
            	    'body' => $company,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $company
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductCarCompany $productCarCompany)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'en_title'=> 'nullable|string',
            'order' => 'required|numeric',
            'image_url' => 'nullable|string'
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

        $productCarCompany->update([
            'title' => $request->title,
            'en_title'=> $request->en_title,
            'order' => $request->order,
            'image_url' => $img,
        ]);

        ProductCarTypes::where("company_id",$productCarCompany->id)->update([
            "company_name"=> $request->title
        ]);

        ProductDefinedCar::where("company_id",$productCarCompany->id)->update([
            "company_name"=> $request->title
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "ویرایش شرکت خودروسازی",
            	    'body' => $productCarCompany,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productCarCompany
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,ProductCarCompany $productCarCompany)
    {
        $productCarCompany->delete();

        ProductDefinedCar::where("company_id",$productCarCompany->id)->delete();

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "حذف شرکت خودروسازی",
            	    'body' => $productCarCompany,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productCarCompany
        ], Response::HTTP_OK);
    }
}
