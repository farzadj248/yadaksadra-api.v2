<?php

namespace App\Http\Controllers;

// https://techvblogs.com/blog/laravel-import-export-excel-csv-file

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportUser;
use App\Exports\ExportAdmin;
use App\Exports\ExportProduct;
use App\Exports\ExportOrders;
use App\Exports\ExportProductBrands;
use App\Exports\ExportProductCar;
use App\Exports\ExportProductCarModel;
use App\Exports\ExportProductCarYears;
use App\Exports\ExportProductCategory;
use App\Exports\ExportProductCarCompany;
use App\Exports\ExportProductCountryBuilder;
use App\Imports\ImportProduct;
use App\Imports\ImportProductFromSoftware;
use App\Imports\ImportProductCategory;
use App\Imports\ImportProductCountryBuilders;
use App\Imports\ImportProductCarYears;
use App\Imports\ImportProductCars;
use App\Imports\ImportProductCarModels;
use App\Imports\ImportProductBrands;
use App\Imports\ImportProductPrice;
use App\Imports\ImportProductCarCompany;

use Carbon\Carbon;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function getReportData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|numeric',
            'userType' => 'numeric',
            'orderStatus' => 'numeric',
            'productStatus' => 'numeric',
            'personelStatus'=> 'numeric'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        switch($request->type){
            case 1:
                Excel::store(new ExportUser((int)$request->userType),"release/export_users.xlsx");
                $link="https://api.yadaksadra.com/local/storage/app/release/export_users.xlsx";
                break;

            case 2:
                Excel::store(new ExportOrders((int)$request->orderStatus),"release/export_orders.xlsx");
                $link="https://api.yadaksadra.com/local/storage/app/release/export_orders.xlsx";
                break;

            case 3:
                Excel::store(new ExportProduct((int)$request->productStatus),"release/export_products.xlsx");
                $link="https://api.yadaksadra.com/local/storage/app/release/export_products.xlsx";
                break;

            case 4:
                Excel::store(new ExportAdmin((int)$request->personelStatus),"release/export_admins.xlsx");
                $link="https://api.yadaksadra.com/local/storage/app/release/export_admins.xlsx";
                break;
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                "link"=> $link
            ]
        ], Response::HTTP_OK);
    }

    // public function exportProductAsSql()
    // {
    //     $tableName = 'products';

    //     $filePath = './storage/database_export/' . date('Y-m-d H:i:s') . '.sql';
    //     $command = "mysqldump -u " . env('DB_USERNAME') . " -p" . env('DB_PASSWORD') . " " . env('DB_DATABASE') . " " . $tableName . " > " . $filePath;
    //     $process = new Process($command);
    //     $process->run();
    //     return response()->json([
    //         'success' => true,
    //         'statusCode' => 201,
    //         'message' => 'عملیات با موفقیت انجام شد.',
    //     ], Response::HTTP_OK);




    //     $file_name  = Carbon::now();
    //     $filePath = './storage/database_export/'.$file_name.'.sql';
    //     $dump = DB::statement("mysqldump -u " . env('DB_USERNAME') . " -p" . env('DB_PASSWORD') . " " . env('DB_DATABASE') . " " . $tableName . " > " . $filePath);



    //     if ($dump) {
    //          return response()->json([
    //                 'success' => true,
    //                 'statusCode' => 201,
    //                 'message' => 'عملیات با موفقیت انجام شد.',
    //         ], Response::HTTP_OK);
    //     } else {
    //          return response()->json([
    //             'success' => true,
    //             'statusCode' => 422,
    //             'message' => 'عملیات ناموفق بود.',
    //         ], Response::HTTP_OK);
    //     }
    // }

    //add product
    public function importProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xls,xlsx',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        Excel::import(new ImportProduct, $request->file('file')->store('files'));

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }


    // public function importProducts(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'file' => 'required|mimes:xls,xlsx',
    //     ]);

    //     if($validator->fails()){
    //         return response()->json([
    //             'success' => false,
    //             'statusCode' => 422,
    //             'message' => $validator->errors()
    //         ], Response::HTTP_OK);
    //     }

    //     Excel::import(new ImportProduct, $request->file('file')->store('files'));

    //     return response()->json([
    //         'success' => true,
    //         'statusCode' => 201,
    //         'message' => 'عملیات با موفقیت انجام شد.',
    //     ], Response::HTTP_OK);
    // }

    //update product price and title
    public function updateProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xls,xlsx',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        Excel::import(new ImportProductPrice, $request->file('file')->store('files'));

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    public function updateProductFromSoftware(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xls,xlsx',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        Excel::import(new ImportProductFromSoftware, $request->file('file')->store('files'));

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    public function importCategories(Request $request){
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xls,xlsx',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        Excel::import(new ImportProductCategory, $request->file('file')->store('files'));

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    public function exportCategories(Request $request){
        Excel::store(new ExportProductCategory((int) $request->parent),"release/export_product_categories.xlsx");
        $link="https://yadaksadra.com/api/local/storage/app/release/export_product_categories.xlsx";

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data'=> $link
        ], Response::HTTP_OK);
    }


    public function importBrands(Request $request){
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xls,xlsx',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        Excel::import(new ImportProductBrands, $request->file('file')->store('files'));

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    public function exportBrands(Request $request){
        Excel::store(new ExportProductBrands((int) $request->parent),"release/export_product_brands.xlsx");
        $link="https://yadaksadra.com/api/local/storage/app/release/export_product_brands.xlsx";

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data'=> $link
        ], Response::HTTP_OK);
    }


    public function importCompany(Request $request){
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xls,xlsx',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        Excel::import(new ImportProductCarCompany, $request->file('file')->store('files'));

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    public function exportCompany(Request $request){
        Excel::store(new ExportProductCarCompany(),"release/export_product_company.xlsx");
        $link="https://yadaksadra.com/api/local/storage/app/release/export_product_company.xlsx";

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data'=> $link
        ], Response::HTTP_OK);
    }


    public function importCars(Request $request){
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xls,xlsx',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        Excel::import(new ImportProductCars, $request->file('file')->store('files'));

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    public function exportCars(Request $request){
        Excel::store(new ExportProductCar(),"release/export_product_cars.xlsx");
        $link="https://yadaksadra.com/api/local/storage/app/release/export_product_cars.xlsx";

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data'=> $link
        ], Response::HTTP_OK);
    }


    public function importCarModels(Request $request){
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xls,xlsx',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        Excel::import(new ImportProductCarModels, $request->file('file')->store('files'));

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    public function exportCarModels(Request $request){
        Excel::store(new ExportProductCarModel((int) $request->id),"release/export_product_cars_model.xlsx");
        $link="https://yadaksadra.com/api/local/storage/app/release/export_product_cars_model.xlsx";

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data'=> $link
        ], Response::HTTP_OK);
    }


    public function importCarYears(Request $request){
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xls,xlsx',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        Excel::import(new ImportProductCarYears, $request->file('file')->store('files'));

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    public function exportCarYears(Request $request){
        Excel::store(new ExportProductCarYears((int) $request->id),"release/export_product_car_years.xlsx");
        $link="https://yadaksadra.com/api/local/storage/app/release/export_product_car_years.xlsx";

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data'=> $link
        ], Response::HTTP_OK);
    }


    public function importCountryBuilders(Request $request){
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xls,xlsx',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        Excel::import(new ImportProductCountryBuilders, $request->file('file')->store('files'));

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    public function exportCountryBuilders(Request $request){
        Excel::store(new ExportProductCountryBuilder(),"release/export_product_country_builders.xlsx");
        $link="https://yadaksadra.com/api/local/storage/app/release/export_product_country_builders.xlsx";

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data'=> $link
        ], Response::HTTP_OK);
    }
}
