<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Orders;
use App\Models\ShippingMethods;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class shippingMethodsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $res = ShippingMethods::where('title', 'like', '%' . $request->q . '%')->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $res,
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
            'body' => 'nullable|string',
            'price' => 'required|numeric',
            'order' => 'required|numeric',
            'status' => 'nullable',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()->toJson()
            ], Response::HTTP_OK);
        }

        $res = ShippingMethods::create([
            'title' => $request->title,
            'body' => $request->body,
            'price' => $request->price,
            'order' => $request->order,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $res
        ], Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ShippingMethods $shippingMethod)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'nullable|string',
            'price' => 'required|numeric',
            'order' => 'required|numeric',
            'status' => 'nullable',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()->toJson()
            ], Response::HTTP_OK);
        }

        $shippingMethod->update([
            'title' => $request->title,
            'body' => $request->body,
            'price' => $request->price,
            'order' => $request->order,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $shippingMethod
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ShippingMethods $shippingMethod)
    {
        if(Orders::where("sending_method",$shippingMethod->id)->exists()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => 'به دلیل وابستگی با سایر بخش ها امکان حذف وجود ندارد.'

            ], Response::HTTP_OK);
        }

        $shippingMethod->delete();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $shippingMethod
        ], Response::HTTP_OK);
    }
}
