<?php

namespace App\Http\Controllers;

use App\Models\Newsletters;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class NewslettersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $newsletters = Newsletters::where('email', 'like', '%' . $request->q . '%')->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            "data" => $newsletters
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
            'email' => 'required|email'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()->toJson()
            ], Response::HTTP_OK);
        }

        $res = Newsletters::where("email",$request->email)->exists();
        if($res){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => 'شما قبلا در خبرنامه یدک صدرا عضو شده اید.',
            ], Response::HTTP_OK);
        }

        Newsletters::create([
            "email"=> $request->email
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'ثبت نام شما در خبرنامه یدک صدرا با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

}
