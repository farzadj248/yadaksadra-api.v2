<?php

namespace App\Http\Controllers;

use App\Models\CreditRequests;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class CreditRequestsController extends Controller
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
    
    public function getUserCreditRequests(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);
        
        $res = CreditRequests::where("user_id",$user->id)
        ->where("status",$request->status)
        ->paginate(10);
        
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $res
        ], Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\creditRequests  $creditRequests
     * @return \Illuminate\Http\Response
     */
    public function show(creditRequests $creditRequests)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\creditRequests  $creditRequests
     * @return \Illuminate\Http\Response
     */
    public function edit(creditRequests $creditRequests)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\creditRequests  $creditRequests
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, creditRequests $creditRequests)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\creditRequests  $creditRequests
     * @return \Illuminate\Http\Response
     */
    public function destroy(creditRequests $creditRequests)
    {
        //
    }
}
