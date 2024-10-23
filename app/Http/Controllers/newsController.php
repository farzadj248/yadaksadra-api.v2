<?php

namespace App\Http\Controllers;

use App\Helper\EventLogs;
use App\Models\Admin;
use App\Models\News;
use App\Models\NewsComments;
use App\Models\NewsCategories;
use App\Helper\GenerateSlug;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class newsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $res1=News::select('title','id','slug','short_body','category_title','category_id','views',
        'rating','image_url','tags','status','created_at')
        ->where('title', 'like', '%' . $request->q . '%');

        if($request->category){
            $res1->where('category_title', 'like', '%' . $request->category . '%');
        }

        if($request->tags){
            $res1->where('tags', 'like', '%' . $request->tags . '%');
        }

        $news=$res1->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $news
        ], Response::HTTP_OK);
    }

    public function getPopularNews(){
        $articles=News::select('title','id','slug','category_id','category_title','views',
        'rating','image_url','created_at')
        ->orderBy("views","desc")
        ->take(6)->get();

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
            'title' => 'required|string|max:255|unique:news',
            'short_body' => 'required|string',
            'long_body' => 'required|string',
            'category_id' => 'required|numeric',
            'category_title' => 'required',
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

        $newsCategories=NewsCategories::where("id",$request->category_id);

        if(!$newsCategories->exists()){
            return response()->json([
                'success' => false,
                'statusCode' => 201,
                'message' => 'دسته بندی یافت نشد.',
            ], Response::HTTP_OK);
        }

        $news = News::create([
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

        $newsCategories->first()->increment('count',1);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "افزودن خبر جدید",
            	    'body' => $news,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $news
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(News $news)
    {
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $news
        ], Response::HTTP_OK);
    }

    public function seen_news(Request $request)
    {
        News::where("id",$request->id)->increment('views',1);;

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
    public function update(Request $request, News $news)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'short_body' => 'required|string',
            'long_body' => 'required|string',
            'category_id' => 'required',
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

        if($request->title != $news->title){
            if(News::where("title",$request->title)->exists()){
                return response()->json([
                    'success' => false,
                    'statusCode' => 201,
                    'message' => [
                        'title'=> 'خبر یا این عنوان قبلأ ثبت شده است.'
                    ],
                ], Response::HTTP_OK);
            }
        }

        if($request->category_id != $news->category_id){
            if(!NewsCategories::where("id",$request->category_id)->exists()){
                return response()->json([
                    'success' => false,
                    'statusCode' => 201,
                    'message' => [
                        'title'=> 'دسته بندی یافت نشد.'
                    ],
                ], Response::HTTP_OK);
            }
        }

        $news->update([
            'title' => $request->title,
            'slug' => GenerateSlug::get($request->title),
            'short_body' => $request->short_body,
            'long_body' => $request->long_body,
            'category_id' => $request->category_id,
            'image_url' => $request->image_url,
            'status' => $request->status,
            'tags'=> $request->tags
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "ویرایش خبر",
            	    'body' => $news,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $news
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,News $news)
    {
        $news->delete();
        NewsCategories::where("id",$news->category_id)->first()->decrement('count',1);
        NewsComments::where("news_id",$news->id)->delete();

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "حذف خبر",
            	    'body' => $news,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $news
        ], Response::HTTP_OK);
    }
}
