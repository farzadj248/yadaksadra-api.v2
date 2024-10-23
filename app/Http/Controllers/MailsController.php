<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\Mail;
use Carbon\Carbon;
use App\Models\Newsletters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use JWT;
use Tymon\JWTAuth\Contracts\JWTSubject as JWTSubject;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

class MailsController extends Controller
{
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'=> 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $details = [
            'view'=> 'mail.themplate1',
            'subject' => $request->subject,
            'body' => $request->body
        ];

        \Mail::to($request->email)->send(new Mail($details));

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.'
        ], Response::HTTP_OK);
    }

    public function send_bulk(Request $request)
    {
        /*
        user_type
        1.all users
        2.normal users
        3.organization users
        4.saler users
        5.marketer users
        6.custom users
        7.Newsletters users
        */

        $validator = Validator::make($request->all(), [
            'user_type' => 'required|numeric',
            'users'=> 'nullable',
            'subject' => 'required|string',
            'body' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        switch($request->user_type){
            case 1:
                $users = User::select("email",'first_name','last_name')->where("email","!=",null)->get();

                $users->map(function($item){
                    $item['name'] = $item->first_name.' '.$item->last_name;
                    return $item;
                });
                break;

            case 2:
                $users = User::select("email",'first_name','last_name')
                ->where("role","Normal")
                ->where("email","!=",null)->get();

                $users->map(function($item){
                    $item['name'] = $item->first_name.' '.$item->last_name;
                    return $item;
                });
                break;

            case 3:
                $users = User::select("email",'first_name','last_name')
                ->where("role","Organization")
                ->where("email","!=",null)->get();

                $users->map(function($item){
                    $item['name'] = $item->first_name.' '.$item->last_name;
                    return $item;
                });
                break;

            case 4:
                $users = User::select("email",'first_name','last_name')
                ->where("role","Saler")
                ->where("email","!=",null)->get();

                $users->map(function($item){
                    $item['name'] = $item->first_name.' '.$item->last_name;
                    return $item;
                });
                break;

            case 5:
                $users = User::select("email",'first_name','last_name')
                ->where("role","Marketer")
                ->where("email","!=",null)->get();

                $users->map(function($item){
                    $item['name'] = $item->first_name.' '.$item->last_name;
                    return $item;
                });
                break;

            case 6:
                $users = json_decode($request->users, true);
                break;

            case 7:
                $users = Newsletters::select("email")->get();

                $users->map(function($item){
                    $item['name'] = "";
                    return $item;
                });
                break;
        }

        if(count($users)==0){
            return response()->json([
                'success' => true,
                'statusCode' => 422,
                'message' => 'کاربری یافت نشد.'
            ], Response::HTTP_OK);
        }

    	$details = [
    	    'view'=> 'mail.themplate1',
    	    'users' => $users,
            'subject' => $request->subject,
            'body' => $request->body
        ];

        $job = (new \App\Jobs\SendQueueEmail($details))
            	->delay(now()->addSeconds(2));

        dispatch($job);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.'
        ], Response::HTTP_OK);
    }
}
