<?php

namespace App\Http\Controllers;

use App\Helper\Sms;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;

class DocumentsController extends Controller
{

    public function index(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                "status"=> $user->documents_status,
                "type"=> $user->credit_purchase_type,
                "document"=> $user->documents,
                "user"=> [
                    "account_status" => $user->status,
                    "documents" => $user->documents,
                    "documents_status" => $user->documents_status,
                    "credit_purchase_type" => $user->credit_purchase_type,
                    "credit_purchase_inventory" => $user->credit_purchase_inventory,
                    "request_credit_again" => $user->request_credit_again
                ]
            ]
        ], Response::HTTP_OK);
    }

    public function activate(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'type' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if($user->status==1){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => "حساب کاربری شما هنوز تأیید نشده است."
            ], Response::HTTP_OK);
        }

        $user=User::where("id",$user->id)->first();

        $user->update([
            "documents"=> '{
                "commitment_letter":{
                    "status": 0,
                    "description": null,
                    "files": []
                },
                "national_card_copy":{
                    "status": 0,
                    "description": null,
                    "files": []
                },
                "business_license_copy": {
                    "status": 0,
                    "description": null,
                    "files": []
                },
                "credit_allocation_request_form":{
                    "status": 0,
                    "description": null,
                    "files": []
                },
                "bank_check":{
                    "status": 0,
                    "description": null,
                    "files": []
                },
                "ice_validation_form":{
                    "status": 0,
                    "description": null,
                    "files": []
                },
                "bank_draft":{
                    "status": 0,
                    "description": null,
                    "files": []
                },
                "introduction_letter":{
                    "status": 0,
                    "description": null,
                    "files": []
                }
            }',
            'credit_purchase_type'=> $request->type
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);

    }

    public function requestCredit(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'type' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if($user->request_credit_again==1){

            if($user->documents_status==2){

                $documents = json_decode($user->documents, true);

                if($documents){

                    if($request->type==3){
                        $documents['business_license_copy']['status']=0;
                    }

                    if($documents['credit_allocation_request_form']['files']){
                        $documents['credit_allocation_request_form']['status']=0;
                    }

                    if($documents['bank_check']['files']){
                        $documents['bank_check']['status']=0;
                    }

                    if($documents['ice_validation_form']['files']){
                        $documents['ice_validation_form']['status']=0;
                    }

                    if($documents['bank_draft']['files']){
                        $documents['bank_draft']['status']=0;
                    }

                    if($documents['introduction_letter']['files']){
                        $documents['introduction_letter']['status']=0;
                    }

                }

                $user->update([
                    'documents'=> $documents,
                    'credit_purchase_type'=> $request->type,
                    'documents_status'=> 0,
                    'request_credit_again'=> 0,
                ]);

                return response()->json([
                    'success' => true,
                    'statusCode' => 201,
                    'message' => 'عملیات با موفقیت انجام شد.',
                ], Response::HTTP_OK);

            }else{
                return response()->json([
                'success' => false,
                    'statusCode' => 422,
                    'message' => 'امکان درخواست اعتبار برای شما وجود ندارد.',
                ], Response::HTTP_OK);
            }

        }else{
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => 'امکان درخواست اعتبار برای شما وجود ندارد جهت ثبت درخواست با پشتیبانی تماس بگیرید.',
            ], Response::HTTP_OK);
        }

    }

    public function update(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'documents' => 'required',
            'status' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if($request->status==1){
            $status = 1;
        }else{
            $status = $user->documents_status;
        }

        User::where("id",$user->id)->first()->update([
            "documents"=> $request->documents,
            'documents_status'=> $status
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    //admin
    public function updateDocument(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'documents' => 'required',
            'status'=> 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $user=User::where("id",$request->id)->first();

        $inventory=$user->credit_purchase_inventory;

        if($request->status==2){
            switch($user->credit_purchase_type){
                case 1:
                    $inventory += 50000000;
                    break;

                case 2:
                    $inventory += 150000000;
                    break;

                case 3:
                    $inventory += 500000000;
                    break;
            }

            if($request->request_credit_again==1){
                $input_data=array("personal_profile_link" => "https://yadaksadra.com/profile/credits");
                Sms::sendWithPatern($user->mobile_number,"jyum7fgdzpbdree",$input_data);
            }
        }

        $user->update([
            "documents"=> $request->documents,
            "documents_status"=> $request->status,
            "credit_purchase_inventory"=> $inventory,
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    public function request_credit_again(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'request_credit_again'=>'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $user=User::where("id",$request->id)->first();

       $user->update([
            "request_credit_again"=> $request->request_credit_again
        ]);

        if($request->request_credit_again==1){
            $input_data=array("personal_profile_link" => "https://yadaksadra.com/profile/credits");
            Sms::sendWithPatern($user->mobile_number,"xthenuduks0d8cu",$input_data);
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }
}
