<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helper\Sms;
use App\Models\Messages;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;

class messageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $messages=Messages::leftJoin('users', function ($query) {
            $query->on('users.id', '=', 'messages.user_id');
        })
            ->select('messages.*','users.first_name','users.last_name')
            ->where('messages.title', 'like', '%' . $request->q . '%')
            ->where("type",$request->type)
            ->orderBy("id","desc")
            ->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $messages
        ], Response::HTTP_OK);
    }

    public function getUserMessages(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $messages=Messages::where('user_id', $user->id)
        ->orderBy("id","desc")
        ->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $messages
        ], Response::HTTP_OK);
    }

    public function unreadMessages(Request $request)
    {
        $messages=Messages::leftJoin('users', function ($query) {
            $query->on('users.id', '=', 'messages.user_id');
        })
            ->select('messages.*','users.first_name','users.last_name')
            ->where("type",1)
            ->where("seen",1)
            ->get();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $messages
        ], Response::HTTP_OK);
    }

    public function seen_message(Request $request){
        $message=Messages::where("id",$request->id)->first();

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
            'title' => 'required|string',
            'body' => 'required|string',
            'mobile_number' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $user = User::where("mobile_number",$request->mobile_number)->first();

        if($user){

            $sms_res = Sms::send($request->mobile_number,$request->body);

            Messages::create([
                "title"=> $request->title,
                "body"=> $request->body,
                "user_id"=> $user->id,
                "type"=> 2
            ]);

            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'پیام با موفقیت ارسال شد.',
                'sms_res'=> $sms_res
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => false,
            'statusCode' => 422,
            'message' => "کاربر یافت نشد"
        ], Response::HTTP_OK);
    }

    public function user_send(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        Messages::create([
            "title"=> $request->title,
            "body"=> $request->body,
            "user_id"=> $user->id,
            "type"=> 1
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'پیام با موفقیت ارسال شد.',
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Messages $message)
    {
        $message->update(['seen'=>0]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
