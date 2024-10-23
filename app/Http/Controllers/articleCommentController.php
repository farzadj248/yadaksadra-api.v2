<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Admin;
use App\Models\ArticleComments;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use App\Helper\EventLogs;
use Illuminate\Support\Facades\Date;

class articleCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $articleComments=ArticleComments::leftJoin('users', function ($query) {
            $query->on('users.id', '=', 'article_comments.user_id');
        })
        ->leftJoin('articles', function ($query) {
            $query->on('articles.id', '=', 'article_comments.article_id');
        })
            ->select('article_comments.*','users.first_name','users.last_name',
            'articles.title as article_title')
            ->where('article_comments.body', 'like', '%' . $request->q . '%')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $articleComments
        ], Response::HTTP_OK);
    }

    public function getUserComments(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $comments=ArticleComments::leftJoin('articles', function ($query) {
            $query->on('articles.id', '=', 'article_comments.article_id');
        })
            ->select('article_comments.*','articles.slug','articles.title as article_title')
            ->where('article_comments.user_id', $user->id)
            ->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $comments
        ], Response::HTTP_OK);
    }

    public function getArticleComments(Request $request)
    {
        $comments = ArticleComments::with('replies')
        ->where('status','=',2)
        ->where('reply_id','=',0)
        ->where('article_id','=',$request->id)
        ->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $comments
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
            'article_id' => 'required|numeric',
            'user_name'=> 'required|string',
            'email' => 'required|email',
            'body' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $comment = ArticleComments::create([
            'reply_id'=> $request->reply_id,
            'article_id'=>$request->article_id,
            'user_name'=>$request->user_name,
            'body'=> $request->body,
            'email'=> $request->email
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'دیدگاه شما با موفقیت ثبت شد.',
            'data' => $comment
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ArticleComments $articleComment)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $articleComment->update([
            'status'=>$request->status,
        ]);

        if($request->status==2){
           Article::where("id",$articleComment->article_id)->increment('comments_number',1);
        }

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "تغییر وضعیت نظر کاربر در رابطه با مقاله",
            	    'body' => $articleComment,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $articleComment
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,ArticleComments $articleComment)
    {
        $articleComment->delete();

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "حذف نظر کاربر در رابطه با مقاله",
            	    'body' => $articleComment,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $articleComment
        ], Response::HTTP_OK);
    }
}
