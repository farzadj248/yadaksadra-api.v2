<?php

namespace App\Http\Controllers;

use App\Helper\EventLogs;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductCarCompany;
use App\Models\ProductCarModels;
use App\Models\ProductDefinedCar;
use App\Models\ProductCarTypes;
use App\Models\ProductCarYear;
use App\Helper\GenerateSlug;
use App\Http\Resources\CommonResources;
use App\Models\ProductCarYears;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App\Models\ProductsProperties;
use App\Models\ProductsDefaultProperty;

class productCarTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->page) {
            $res = ProductCarTypes::where('company_id', $request->id);

            if ($request->q) {
                $res->whereRaw('concat(product_car_types.title,product_car_types.en_title,product_car_types.company_name) like ?', "%{$request->q}%");
            }
            $data = $res->paginate(10);
            return CommonResources::collection($data);
        } else {
            $productCarTypes = ProductCarTypes::where('company_id', $request->id)->get();
        }
        $countries = $productCarTypes->pluck('country')->toArray();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productCarTypes,
            'countries' => $countries
        ], Response::HTTP_OK);
    }


    public function getAllCarCompanies(Request $request)
    {
        $carCompanies = ProductCarCompany::paginate(10);
        $countries = $carCompanies->pluck('country')
            ->unique()
            ->filter()
            ->toArray();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $carCompanies,
            'countries' =>  $countries
        ], Response::HTTP_OK);
    }


    public function getCarTypes(Request $request)
    {

        $company = ProductCarCompany::select("id", "title", "image_url as image")->where("id", $request->id)->first();
        $cars = [];
        if ($company) {
            $cars = ProductCarTypes::select("id", "title", "en_title", "image_url")
                ->where('company_id', $company->id)->get();
            foreach ($cars as $car) {
                $models = ProductCarModels::where("car_id", $car->id)->exists();
                $car['hasChild'] = $models;
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'company' => $company,
                'cars' => $cars
            ],
        ], Response::HTTP_OK);
    }

    public function getProfessional(Request $request)
    {
        // $productsProperties = ProductsProperties::where('value', ['نوع قطعه', 'برند قطعه'])->get();
        $productsProperties = ProductsDefaultProperty::whereIn('value', ['نوع قطعه', 'برند قطعه' , 'نوع موتور'])->get();
        // $typeParts = ProductsProperties::where('value', 'نوع قطعه')->get();
        // // $brandParts = ProductsProperties::where('value', 'برند قطعه')->get();
        // // $engineTypes = ProductsProperties::where('value', 'نوع موتور')->get();
        // // $typeParts = ProductsProperties::where('value', 'نوع قطعه')->pluck('child');
        // // $brandParts = ProductsProperties::where('value', 'برند قطعه')->pluck('child');
        // // $engineTypes = ProductsProperties::where('value', 'نوع موتور')->pluck('child');
        $typeParts = ProductsDefaultProperty::where('title', 'نوع قطعه')->pluck('value');
        $brandParts = ProductsDefaultProperty::where('title', 'برند قطعه')->pluck('value');
        $engineTypes = ProductsDefaultProperty::where('title', 'نوع موتور')->pluck('value');

      

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'productsProperties' => $productsProperties,
                'typeParts' => $typeParts,
                'brandParts' => $brandParts,
                'engineTypes' => $engineTypes,

            ],
        ], Response::HTTP_OK);
    }
    public function getCarYears(Request $request)
    {
        $carYears = ProductCarYears::where('model_id', $request->id)->get();
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'cars years' => $carYears
            ],
        ], Response::HTTP_OK);
    }


    public function getCarModels(Request $request)
    {
        $models = ProductCarModels::where("car_id", $request->id)->get();
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'models' => $models
            ],
        ], Response::HTTP_OK);
    }



    public function getCarsWithName(Request $request)
    {
        $company = ProductCarCompany::select("id", "title", "image_url as image")->where("title", $request->title)->first();

        $cars = [];
        if ($company) {
            $cars = ProductCarTypes::select("id", "title", "en_title", "image_url")
                ->where('company_id', $company->id)->get();

            foreach ($cars as $car) {
                $models = ProductCarModels::where("car_id", $car->id)->exists();
                $car['hasChild'] = $models;
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'company' => $company,
                'cars' => $cars
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
            'id' => 'required|numeric',
            'company' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'en_title' => 'nullable|string',
            'order' => 'required|numeric',
            'image_url' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if ($request->image_url) {
            $img = $request->image_url;
        } else {
            $img = "https://dl.yadaksadra.com/storage/images/not-icon.svg";
        }

        $productCarType = ProductCarTypes::create([
            'company_id' => $request->id,
            'company_name' => $request->company,
            'title' => $request->title,
            'order' => $request->order,
            'en_title' => $request->en_title,
            'image_url' => $img,
        ]);

        if ($request->header('agent')) {
            $admin = Admin::where("id", $request->header('agent'))->first();

            if ($admin) {
                EventLogs::addToLog([
                    'subject' => "افزودن خودروی جدید",
                    'body' => $productCarType,
                    'user_id' => $admin->id,
                    'user_name' => $admin->first_name . " " . $admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productCarType
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductCarTypes $productCarType)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'en_title' => 'nullable|string',
            'order' => 'required|numeric',
            'image_url' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if ($request->image_url) {
            $img = $request->image_url;
        } else {
            $img = "https://dl.yadaksadra.com/storage/images/not-icon.svg";
        }

        $productCarType->update([
            'title' => $request->title,
            'en_title' => $request->en_title,
            'order' => $request->order,
            'image_url' => $img,
        ]);

        ProductDefinedCar::where("car_id", $productCarType->id)->update([
            "car_name" => $request->title
        ]);

        if ($request->header('agent')) {
            $admin = Admin::where("id", $request->header('agent'))->first();

            if ($admin) {
                EventLogs::addToLog([
                    'subject' => "ویرایش شرکت خودروسازی",
                    'body' => $productCarType,
                    'user_id' => $admin->id,
                    'user_name' => $admin->first_name . " " . $admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productCarType
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, ProductCarTypes $productCarType)
    {
        $productCarType->delete();

        ProductDefinedCar::where("car_id", $productCarType->id)->delete();

        if ($request->header('agent')) {
            $admin = Admin::where("id", $request->header('agent'))->first();

            if ($admin) {
                EventLogs::addToLog([
                    'subject' => "حذف شرکت خودروسازی",
                    'body' => $productCarType,
                    'user_id' => $admin->id,
                    'user_name' => $admin->first_name . " " . $admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productCarType
        ], Response::HTTP_OK);
    }
}
