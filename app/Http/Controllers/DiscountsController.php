<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Discounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class DiscountsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $discounts=Discounts::where('title', 'like', '%' . $request->q . '%')->paginate(10);
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $discounts
        ], Response::HTTP_OK);
    }

    public function getUserDiscounts(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $discounts=Discounts::where("creator_id",$user->id)
        ->where("status",$request->status)
        ->where("creator",2)
        ->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $discounts
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
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'code' => 'required|string|unique:discounts',
            'user_type' => 'nullable|numeric',
            'type' => 'required|numeric',
            'user_limit' => 'nullable',
            'number_use_limit' => 'required|numeric',
            'status' => 'required|numeric',
            'expire_date' => 'required|string',
            'start_date' => 'required|string',
            'creator' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $discountCode = Discounts::create([
            'creator_id'=> $user->id,
            'title' => $request->title,
            'code' => $request->code,
            'user_type' => $request->user_type,
            'type' => $request->type,
            'value' => $request->value,
            'user_limit' => $request->user_limit,
            'number_use_limit' => $request->number_use_limit,
            'status' => $request->status,
            'expire_date' => $request->expire_date,
            'start_date' => $request->start_date,
            'creator' => $request->creator
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $discountCode
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Discounts  $discounts
     * @return \Illuminate\Http\Response
     */
    public function show(Discounts $discount)
    {
        $user =null;

        if($discount->creator==2){
            $user = User::select("first_name","last_name","user_name")
            ->where("id",$discount->creator_id)->first();
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                "discount"=> $discount,
                "user"=> $user
            ]
        ], Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Discounts  $discounts
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Discounts $discount)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'code' => 'required|string',
            'user_type' => 'required|numeric',
            'type' => 'required|string',
            'user_limit' => 'nullable|string',
            'number_use_limit' => 'required|string',
            'status' => 'required|string',
            'expire_date' => 'required|string',
            'start_date' => 'required|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if($request->code != $discount->code){
            if(Discounts::where("code",$request->code)->exists()){
                return response()->json([
                    'success' => false,
                    'statusCode' => 201,
                    'message' => [
                        'title'=> 'مقاله یا این عنوان قبلأ ثبت شده است.'
                    ],
                ], Response::HTTP_OK);
            }
        }

        $discount->update([
            'title' => $request->title,
            'code' => $request->code,
            'user_type' => $request->user_type,
            'type' => $request->type,
            'value' => $request->value,
            'user_limit' => $request->user_limit,
            'number_use_limit' => $request->number_use_limit,
            'status' => $request->status,
            'rejected_reason' => $request->rejected_reason,
            'expire_date' => $request->expire_date,
            'start_date' => $request->start_date
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $discount
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Discounts  $discounts
     * @return \Illuminate\Http\Response
     */
    public function destroy(Discounts $discount)
    {
        $discount->delete();
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $discount
        ], Response::HTTP_OK);
    }
}
