<?php

namespace App\Http\Controllers;

use App\Helper\EventLogs;
use App\Models\Admin;
use App\Models\NewsComments;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class newsCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $newsComments=NewsComments::leftJoin('users', function ($query) {
            $query->on('users.id', '=', 'news_comments.user_id');
        })
        ->leftJoin('news', function ($query) {
            $query->on('news.id', '=', 'news_comments.news_id');
        })
            ->select('news_comments.*','users.first_name','users.last_name',
            'news.title as news_title')
            ->where('news_comments.body', 'like', '%' . $request->q . '%')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $newsComments
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
            'reply_id' => 'required|numeric',
            'news_id' => 'required|numeric',
            'user_id' => 'required|numeric',
            'body' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $newsComment = NewsComments::create([
            'reply_id'=> $request->reply_id,
            'news_id'=>$request->news_id,
            'user_id'=>$request->user_id,
            'body'=> $request->body
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $newsComment
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NewsComments $newsComment)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|numeric',
            'rejected_reason' => 'string'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $newsComment->update([
            'status'=>$request->status,
            'rejected_reason' => $request->status==3?$request->rejected_reason:null
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "تغییر وضعیت نظر کاربر در رابطه با خبر",
            	    'body' => $newsComment,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $newsComment
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(NewsComments $newsComment)
    {
        $newsComment->delete();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $newsComment
        ], Response::HTTP_OK);
    }
}
