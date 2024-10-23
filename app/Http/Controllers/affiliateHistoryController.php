<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AffiliateHistory;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class affiliateHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
            'uuid' => 'required|string',
            'affiliate_id' => 'numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $affiliateHistory = AffiliateHistory::where("uuid",$request->uuid)
        ->where("affiliate_id",$request->affiliate_id)
        ->exists();

        if(!$affiliateHistory){
            AffiliateHistory::create([
                "uuid"=> $request->uuid,
                "affiliate_id"=> $request->affiliate_id
            ]);

            $aff_user = User::where("id",$request->affiliate_id)->first();

            if($aff_user){
                $aff_user->increment('invited_affiliate_pending',1);
                $aff_user->increment('clicks',1);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

}
