<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helper\Sms;
use App\Models\DepositRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;


class depositRequestsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $depositRequests=DepositRequests::leftJoin('users', function ($query) {
            $query->on('users.id', '=', 'deposit_requests.user_id');
        })
            ->select('deposit_requests.*','users.first_name','users.last_name','users.shaba_bank')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $depositRequests
        ], Response::HTTP_OK);
    }

    public function getUserDepositRequests(Request $request)
    {
        $res = DepositRequests::where("user_id",$request->id)->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $res
        ], Response::HTTP_OK);
    }

    public function userDepositRequests(Request $request){
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $depositRequests=DepositRequests::where("user_id",$user->id)->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $depositRequests
        ], Response::HTTP_OK);
    }

    public function update(Request $request, DepositRequests $depositRequest)
    {
        if($request->status==2){
            $validator = Validator::make($request->all(), [
                'file' => 'required|string',
                'status' => 'required|required',
                'transaction_code'=> 'required'
            ]);
        }else{
            $validator = Validator::make($request->all(), [
                'file' => 'required|string',
                'rejected_reason' => 'nullable',
                'status' => 'required|required',
                'transaction_code'=> 'nullable'
            ]);
        }

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $user = User::where("id",$depositRequest->user_id)->first();

        if($request->status==2){
            $balance = (int)$user->wallet_balance;
            $amount = (int)$depositRequest->amount;

            if($amount <= $balance){
                $final = $balance-$amount;

                if($depositRequest->status==2 or $depositRequest->status==3){
                    $user->update([
                        "wallet_balance" => $final
                    ]);
                }
            }

            $depositRequest->update([
                "status" => $request->status,
                "file" => $request->file,
                "rejected_reason" => "",
                "transaction_code"=> $request->transaction_code
            ]);

            $input_data=array(
                "amount" => number_format($depositRequest->amount).' تومان',
                "id"=> $request->transaction_code
            );
            // Sms::sendWithPatern($user->mobile_number,"zj0tamx2my6zpsp",$input_data);
        }else{
            $depositRequest->update([
                "status" => $request->status,
                "file"=> "",
                "transaction_code"=> "",
                "rejected_reason" => $request->rejected_reason,
            ]);
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                "user" => [
                    "user_id"=> $user->id,
                    "first_name"=> $user->first_name,
                    "last_name"=> $user->last_name,
                    "shaba_bank"=> $user->shaba_bank,
                ],
                "depositRequest" => $depositRequest
            ]
        ], Response::HTTP_OK);
    }

    public function withdraw_wallet(Request $request){
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:100000'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $depositRequests=DepositRequests::where("user_id",$user->id)
        ->where("status",1)->first();

        if($depositRequests){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => [
                    'title'=> "یک درخواست در حال پردازش است،امکان درخواست دیگر وجود ندارد."
                ]
            ], Response::HTTP_OK);
        }else{
            if($user->shaba_bank){

                DepositRequests::create([
                    "user_id"=> $user->id,
                    "amount"=> $request->amount,
                    "shaba_bank"=> 'IR'.$user->shaba_bank
                ]);

                return response()->json([
                    'success' => true,
                    'statusCode' => 201,
                    'message' => 'درخواست شما با موفقیت ثبت شد.',
                ], Response::HTTP_OK);

            }else{

                 return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => [
                        'title'=> "برای ثبت درخواست ابتدا از بخش اطلاعات کاربری شماره شبا بانکی خود را ثبت نمایید."
                    ]
                ], Response::HTTP_OK);

            }

        }
    }

}
