<?php

namespace App\Http\Controllers;

use App\Helper\EventLogs;

use App\Models\Admin;
use App\Models\Reviews;
use App\Models\Product;
use App\Models\ProductComments;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;

class productCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $productComments=ProductComments::leftJoin('users', function ($query) {
            $query->on('users.id', '=', 'product_comments.user_id');
        })
        ->leftJoin('products', function ($query) {
            $query->on('products.id', '=', 'product_comments.product_id');
        })
            ->select('product_comments.*','users.first_name','users.last_name',
            'products.title as products_title')
            ->where('product_comments.body', 'like', '%' . $request->q . '%')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productComments
        ], Response::HTTP_OK);
    }

    public function getUserComments(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $comments=ProductComments::leftJoin('products', function ($query) {
            $query->on('products.id', '=', 'product_comments.product_id');
        })
            ->select('product_comments.*','products.slug','products.title as products_title')
            ->where('product_comments.user_id',$user->id)
            ->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $comments
        ], Response::HTTP_OK);
    }

    public function getProductComments(Request $request){
        $comments = ProductComments::with('replies')
        ->where('status','=',2)
        ->where('reply_id','=',0)
        ->where('product_id','=',$request->product_id)
        ->paginate(10);

        $total_scorre = 0;
        foreach($comments as $comment){
            $total_scorre+=$comment->score;
        }

        $comments->setCollection(
            $comments->getCollection()
                ->map(function($item) use ($request)
                {
                    $reviews=Reviews::select("positive_score","negative_score")->where("post_id",$item->id)
                    ->where("user_id",$request->id)
                    ->where("post_type",1)
                    ->first();

                    if($reviews){
                        if($reviews->positive_score==1){
                           $item['action'] = 1;
                        }else{
                            $item['action'] = 2;
                        }
                    }

                    return $item;
                })
        );

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
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|numeric',
            'subject'=> 'required|string',
            'body' => 'required|string',
            'score' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $comment = ProductComments::create([
            'product_id'=>$request->product_id,
            'user_id'=>$user->id,
            'user_name'=> $user->first_name.' '.$user->last_name,
            'subject'=> $request->subject,
            'body'=> $request->body,
            'score'=> $request->score
        ]);

        $comments = ProductComments::select("score")
        ->where("status",2)
        ->where("product_id",$request->product_id)
        ->get();

        $sum = 0;
        foreach($comments as $item){
            $sum+=(int)$item->score;
        }

        if($comments->count()==0 and $sum==0){
            $score = $request->score;
        }else{
            $score =($sum) / ($comments->count());
        }

        $product = Product::where("id",$request->product_id)->first();

        $product->update([
            'rating'=> $score
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'دیدگاه شما با موفقیت ثبت شد.',
            'data' => $comment
        ], Response::HTTP_OK);
    }

    public function changeScoreComment(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'comment_id' => 'required|numeric',
            'type'=> 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $comment = ProductComments::where("id",$request->comment_id)->first();

        $reviews = Reviews::where("post_id",$request->comment_id)
        ->where("user_id",$user->id)
        ->where("post_type",1)
        ->first();

        if($reviews){
            if($request->type==1){
                if($reviews->positive_score==1){
                    if($comment->positive_score>0){
                       $comment->decrement('positive_score',1);
                    }

                    $reviews->delete();

                    $action = 0;
                }else{
                    if($comment->negative_score>0){
                       $comment->decrement('negative_score',1);
                    }

                    $comment->increment('positive_score',1);

                    $reviews->update([
                        'positive_score'=> 1,
                        'negative_score'=> 0
                    ]);

                    $action = 1;
                }
            }else{
                if($reviews->negative_score==1){
                    if($comment->negative_score>0){
                       $comment->decrement('negative_score',1);
                    }

                    $reviews->delete();

                    $action=0;
                }else{
                    $comment->increment('negative_score',1);
                    if($comment->positive_score>0){
                       $comment->decrement('positive_score',1);
                    }

                    $reviews->update([
                        'positive_score'=> 0,
                        'negative_score'=> 1
                    ]);

                    $action = 2;
                }

            }

        }else{
            if($request->type==1){
                $reviews = Reviews::create([
                    "user_id"=>$user->id,
                    "post_type"=>1,
                    "post_id"=>$request->comment_id,
                    "positive_score"=> 1
                ]);

                $comment->increment('positive_score',1);

                $action = 1;
            }else{
                $reviews = Reviews::create([
                    "user_id"=>$user->id,
                    "post_type"=>1,
                    "post_id"=>$request->comment_id,
                    "negative_score"=> 1
                ]);

                $comment->increment('negative_score',1);

                $action = 2;
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'id'=> $comment->id,
                'positive_score'=> $comment->positive_score,
                'negative_score'=> $comment->negative_score,
                'action'=> $action
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductComments $productComment)
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

        $productComment->update([
            'status'=>$request->status,
            'rejected_reason' => $request->status==3?$request->rejected_reason:null
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "تغییر وضعیت نظر کاربر برای محصول",
            	    'body' => $productComment,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productComment
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductComments $productComment)
    {
        $productComment->delete();
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $productComment
        ], Response::HTTP_OK);
    }
}
