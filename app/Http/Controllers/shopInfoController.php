<?php

namespace App\Http\Controllers;

use App\Models\ShopInfo;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class shopInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shopInfo=ShopInfo::first();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $shopInfo
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ShopInfo $shopInfo)
    {
        $validator = Validator::make($request->all(), [
            'province' => 'required|string',
            'provinceId' => 'required|numeric',
            'city' => 'required|string',
            'cityId' => 'required|numeric',
            'email' => 'required|string',
            'address' => 'required|string',
            'postal_code' => 'required|numeric',
            'support_phone' => 'required|numeric',
            'support_mobile_number'=> 'nullable|numeric',
            'whatsapp_number' => 'required|numeric',
            'telegram_number' => 'required|numeric',
            'other_percent_purchase' => 'nullable|numeric',
            'marketer_percent_purchase' => 'nullable|numeric',
            'catalog' => 'nullable'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $shopInfo->update([
            'province' => $request->province,
            'provinceId' => $request->provinceId,
            'city' => $request->city,
            'cityId' => $request->cityId,
            'address' => $request->address,
            'postal_code' => $request->postal_code,
            'support_phone' => $request->support_phone,
             'support_mobile_number' => $request->support_mobile_number,
            'whatsapp_number' => $request->whatsapp_number,
            'telegram_number' => $request->telegram_number,
            'email' => $request->email,
            'other_percent_purchase' => $request->other_percent_purchase,
            'marketer_percent_purchase' => $request->marketer_percent_purchase,
            'catalog' => $request->catalog
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $shopInfo
        ], Response::HTTP_OK);
    }

    public function termsAndConditions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'terms_and_conditions' => 'nullable'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        ShopInfo::where("id",$request->id)->update([
            'terms_and_conditions' => $request->terms_and_conditions
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    public function about(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'description' => 'nullable',
            'briefly_about'=> 'nullable',
            'image'=> 'nullable'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        ShopInfo::where("id",$request->id)->update([
            'about' => $request->description,
            "briefly_about"=> $request->briefly_about,
            'image' => $request->image
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }
}
