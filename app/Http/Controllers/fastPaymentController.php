<?php

namespace App\Http\Controllers;

use App\Models\FastPayment;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

use Shetabit\Multipay\Invoice;
use Shetabit\Multipay\Payment;

class fastPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $transactions=FastPayment::paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $transactions
        ], Response::HTTP_OK);
    }

    public function getUserTransactions(Request $request){
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $transactions=FastPayment::select("amount","mobile_number","description", "status", "SaleReferenceId as id","updated_at as dateTime")
        ->where("mobile_number",$user->mobile_number)
        ->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $transactions
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
        if($request->id!=0){
            $transaction=FastPayment::where("SaleReferenceId",$request->id)->first();
            if(!$transaction){
                 return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => [
                        'title'=> 'تراکنش مورد نظر یافت نشد!'
                    ]
                ], Response::HTTP_OK);
            }
        }else{
            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string',
                'amount' => 'required|numeric',
                'email' => 'nullable|email',
                'mobile_number' => 'required|numeric',
                'address' => 'nullable|string',
                'description' => 'required|string',
            ]);

            if($validator->fails()){
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => $validator->errors()
                ], Response::HTTP_OK);
            }

            $last_payment = FastPayment::latest()->first();
            if($last_payment){
                $transaction_code = (int)$last_payment->SaleReferenceId + 1;
            }else{
               $transaction_code =  10000;
            }

            $transaction = FastPayment::create([
                'full_name' => $request->full_name,
                'amount' => $request->amount,
                'email' => $request->email,
                'mobile_number' => $request->mobile_number,
                'address' => $request->address,
                'description' => $request->description,
                'SaleReferenceId'=> $transaction_code
            ]);
        }

        return $this->melli($transaction,(int)$transaction->amount);
    }

    public function melli($transaction,$amount){
        $paymentConfig = require('./local/config/payment.php');

        $payment = new Payment($paymentConfig);

        $invoice = (new Invoice)->amount($amount);

        $payment->via('sadad');

        $res=$payment->callbackUrl('https://api.yadaksadra.com/verify')->purchase($invoice,function($driver, $transactionId) use ($transaction) {
            	$transaction->update([
                    "SaleOrderId"=> $transactionId,
                    "gateway_pay"=> 4
                ]);
        })->pay();

        if($res){
            return response()->json([
                'success' => true,
                'statusCode' => 200,
                'message' => "عملیات با موفقیت انجام شد.",
                'status_py'=> 4,
                'data' => $res
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => false,
            'statusCode' => 422,
            'message' => "عملیات با خطا مواجه شد.",
            'data' => null
        ], Response::HTTP_OK);
    }

}
