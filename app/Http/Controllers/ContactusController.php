<?php

namespace App\Http\Controllers;

use App\Models\Contactus;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class ContactusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $contactus = Contactus::where('subject', 'like', '%' . $request->q . '%')->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            "data" => $contactus
        ], Response::HTTP_OK);
    }

    public function seen_message(Request $request){
        $message=Contactus::where("id",$request->id)->first();

        if($message){
            $message->update([
                "seen"=> 0
            ]);
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
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
            'email' => 'required|email',
            "name" => 'required|string',
            "mobile_number" => 'required|numeric',
            "subject" => 'required|string',
            "message" => 'required|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }


        Contactus::create([
            "name"=> $request->name,
            "email"=> $request->email,
            "mobile_number"=> $request->mobile_number,
            "subject"=> $request->subject,
            "message"=> $request->message
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'درخواست شما با موفقیت ثبت شد.',
        ], Response::HTTP_OK);
    }
}
