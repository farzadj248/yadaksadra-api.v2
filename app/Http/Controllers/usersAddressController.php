<?php

namespace App\Http\Controllers;

use App\Models\UsersAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use JWT;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject as JWTSubject;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

class usersAddressController extends Controller
{
    public function getAddress(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $address = UsersAddress::where('user_id',$user->id)
        ->where('default',1)
        ->where('type',1)
        ->select('id','province_id','province','city_id','city', 'address', 'plaque', 'floor','building_unit', 'postal_code', 'default', 'type')
        ->first();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $address
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(UsersAddress $usersAddress)
    {
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $usersAddress
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateAddress(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'province_id' => 'required|numeric',
            'province' => 'required',
            'city_id' => 'required|numeric',
            'city' => 'required|string',
            'address' => 'required|string',
            'type' => 'required|numeric',
            'default' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $address = UsersAddress::where('user_id',$user->id)
        ->where("id",$request->id)
        ->first();

        if($address){
            $address->update([
                'province_id'=> $request->province_id,
                'province'=> $request->province,
                'city_id'=> $request->city_id,
                'city'=> $request->city,
                'address'=> $request->address,
                'plaque'=> $request->plaque,
                'floor'=> $request->floor,
                'building_unit'=> $request->building_unit,
                'postal_code'=> $request->postal_code,
                'type'=> $request->type,
                'default'=> $request->default,
            ]);
        }else{
            UsersAddress::create([
                'user_id'=> $user->id,
                'province_id'=> $request->province_id,
                'province'=> $request->province,
                'city_id'=> $request->city_id,
                'city'=> $request->city,
                'address'=> $request->address,
                'plaque'=> $request->plaque,
                'floor'=> $request->floor,
                'building_unit'=> $request->building_unit,
                'postal_code'=> $request->postal_code,
                'type'=> $request->type,
                'default'=> $request->default,
            ]);
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

}
