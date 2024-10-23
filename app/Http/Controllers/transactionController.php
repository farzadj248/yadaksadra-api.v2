<?php


namespace App\Http\Controllers;

use App\Models\Transactions;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;


class transactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $transactions=Transactions::leftJoin('users', function ($query) {
            $query->on('users.id', '=', 'transactions.user_id');
        })
            ->where('description', 'like', '%' . $request->q . '%')
            // ->where("type",1)
            ->select('transactions.*','users.first_name','users.last_name')
            ->paginate(10);
        
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $transactions
        ], Response::HTTP_OK);
    }
    
    public function getUserTransactions(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);
        
        $transactions=Transactions::where('user_id',$user->id)->paginate(10);
        
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $transactions
        ], Response::HTTP_OK);
    }

    public function unPayedTransactions(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);
        
        $transactions=Transactions::where('user_id',$user->id)
        ->where("status",1)
        ->paginate(10);
        
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $transactions
        ], Response::HTTP_OK);
    }
}
