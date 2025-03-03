<?php

namespace App\Http\Controllers;

use App\Helper\EventLogs;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductCarYears;
use App\Models\ProductCarTypes;
use App\Models\ProductCarModels;
use App\Models\ProductDefinedCar;
use App\Helper\GenerateSlug;
use App\Http\Resources\CommonResources;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class productCarModelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->page){
            if($request->filled('id')){
                $res=ProductCarModels::where("car_id",$request->id);
            }else{
                $res=ProductCarModels::where("car_name",$request->model);
            }
            if($request->q){
                $res->whereRaw('concat(product_car_models.title,product_car_models.en_title,product_car_models.model) like ?', "%{$request->q}%");
            }
            $data = $res->paginate(10);
            return CommonResources::collection($data);
        }else{
            if($request->id){
                $ProductCarModels=ProductCarModels::select("product_car_models.id","product_car_models.title", "product_car_models.en_title","product_car_models.car_id", "product_car_models.model",
                "product_car_models.body", "product_car_models.image_url")
                ->where("product_car_models.car_id",$request->id)
                ->get();
            }else{
                $ProductCarModels=ProductCarModels::select("product_car_models.id","product_car_models.title", "product_car_models.en_title","product_car_models.car_id", "product_car_models.model",
                "product_car_models.body", "product_car_models.image_url")
                ->where("product_car_models.car_name",$request->model)
                ->get();

                foreach($ProductCarModels as $model){
                    $hasChild=ProductCarYears::where("model_id",$model->id)->exists();
                    $model['hasChild']=$hasChild;
                }
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $ProductCarModels
        ], Response::HTTP_OK);
    }

    public function getModels(Request $request)
    {
        $company=ProductCarTypes::select("id","title","image_url as image")->where("title",$request->title)->first();

        $cars=[];
        if($company){
            $cars=ProductCarModels::where("car_id",$company->id)->get();
        }


        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'company'=> $company,
                'cars'=> $cars
            ],
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
            'en_title' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'car_id' => 'required|numeric',
            'car_name' => 'nullable|string',
            'body' => 'nullable|string',
            'image_url'=> 'nullable'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $ProductCarModels = ProductCarModels::create([
            'title' => $request->title,
            'en_title' => $request->en_title,
            'model' => $request->model,
            'car_id' => $request->car_id,
            'car_name' => $request->car_name,
            'body' => $request->body,
            'image_url'=> $request->image_url?$request->image_url:"https://dl.yadaksadra.com/web/vehicle-details-default.png"
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "ایجاد مدل جدید برای خودرو",
            	    'body' => $ProductCarModels,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $ProductCarModels
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductCarModels $productCarModel)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'en_title' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'car_id' => 'required|numeric',
            'car_name' => 'nullable|string',
            'body' => 'nullable|string',
            'image_url'=> 'nullable'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $productCarModel->update([
            'title' => $request->title,
            'en_title' => $request->en_title,
            'model' => $request->model,
            'car_id' => $request->car_id,
            'car_name' => $request->car_name,
            'body' => $request->body,
            'image_url'=> $request->image_url?$request->image_url:"https://dl.yadaksadra.com/web/vehicle-details-default.png"
        ]);

        ProductDefinedCar::where("model_id",$productCarModel->id)->update([
            "model_name"=> $request->title
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "ویرایش مدل خودرو",
            	    'body' => $productCarModel,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productCarModel
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,ProductCarModels $productCarModel)
    {
        $productCarModel->delete();

        ProductDefinedCar::where("model_id",$productCarModel->id)->delete();

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "حذف مدل خودرو",
            	    'body' => $productCarModel,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productCarModel
        ], Response::HTTP_OK);
    }
}
