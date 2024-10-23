<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Article;
use App\Models\User;
use App\Models\ArticleComments;
use App\Models\ArticleCategories;
use App\Helper\GenerateSlug;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App\Helper\EventLogs;
use Illuminate\Support\Facades\Date;

class articleController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $res1=Article::select('title','id','slug','short_body','category_title','tags','category_id','views',
        'rating','comments_number','image_url','status','created_at')
        ->where('title', 'like', '%' . $request->q . '%');

        if($request->category){
            $res1->where('category_title', 'like', '%' . $request->category . '%');
        }

        if($request->tags){
            $res1->where('tags', 'like', '%' . $request->tags . '%');
        }

        $articles=$res1->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $articles
        ], Response::HTTP_OK);
    }

    public function getPopularArticles(){
        $articles=Article::select('title','id','slug','category_id','category_title','views',
        'rating','comments_number','image_url','created_at')
        ->orderBy("views","desc")
        ->take(6)->get();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $articles
        ], Response::HTTP_OK);
    }

    public function getRelatedArticles(Request $request){
        $articles=Article::select('title','id','slug','category_id','category_title','views',
        'rating','comments_number','image_url','created_at')
        ->orderBy("id","desc")
        ->where("category_id",$request->category)
        ->take(6)
        ->get();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $articles
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
            'title' => 'required|string|max:255|unique:articles',
            'short_body' => 'required|string',
            'long_body' => 'required|string',
            'category_id' => 'required|numeric',
            'category_title' => 'required|string',
            'image_url' => 'required|string',
            'status' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()->toJson()
            ], Response::HTTP_OK);
        }

        $articleCategories=ArticleCategories::where("id",$request->category_id);

        if(!$articleCategories->exists()){
            return response()->json([
                'success' => false,
                'statusCode' => 201,
                'message' => 'دسته بندی یافت نشد.',
            ], Response::HTTP_OK);
        }

        $article = Article::create([
            'title' => $request->title,
            'slug' => GenerateSlug::get($request->title),
            'short_body' => $request->short_body,
            'long_body' => $request->long_body,
            'category_id' => $request->category_id,
            'category_title' => $request->category_title,
            'image_url' => $request->image_url,
            'status' => $request->status,
            'tags'=> $request->tags
        ]);

        $articleCategories->first()->increment('count',1);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "ایجاد مقاله جدید",
            	    'body' => $article,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $article
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Article $article)
    {
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $article
        ], Response::HTTP_OK);
    }

    public function seen_article(Request $request)
    {
        Article::where("id",$request->id)->increment('views',1);;

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.'
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Article $article)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'short_body' => 'required|string',
            'long_body' => 'required|string',
            'category_id' => 'required|numeric',
            'category_title' => 'required|string',
            'image_url' => 'required|string',
            'status' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if($request->title != $article->title){
            if(Article::where("title",$request->title)->exists()){
                return response()->json([
                    'success' => false,
                    'statusCode' => 201,
                    'message' => [
                        'title'=> 'مقاله یا این عنوان قبلأ ثبت شده است.'
                    ],
                ], Response::HTTP_OK);
            }
        }

        if($request->category_id != $article->category_id){
            if(!ArticleCategories::where("id",$request->category_id)->exists()){
                return response()->json([
                    'success' => false,
                    'statusCode' => 201,
                    'message' => [
                        'title'=> 'دسته بندی یافت نشد.'
                    ],
                ], Response::HTTP_OK);
            }
        }

        $article->update([
            'title' => $request->title,
            'slug' => GenerateSlug::get($request->title),
            'short_body' => $request->short_body,
            'long_body' => $request->long_body,
            'category_id' => $request->category_id,
            'category_title' => $request->category_title,
            'image_url' => $request->image_url,
            'status' => $request->status,
            'tags'=> $request->tags
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "ویرایش مقاله",
            	    'body' => $article,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $article
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,Article $article)
    {
        $article->delete();
        ArticleCategories::where("id",$article->category_id)->first()->decrement('count',1);
        ArticleComments::where("article_id",$article->id)->delete();

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::save([
                    "id"=> $admin->id,
                    "user_name"=> $admin->user_name,
                    "full_name"=> $admin->first_name." ".$admin->last_name,
                    "content"=> "حذف مقاله <br>".$article->title,
                    "created_at"=> Date::now()->format('Y-m-d H:i:s')
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $article
        ], Response::HTTP_OK);
    }
}
