<?php

namespace App\Http\Controllers;

use App\Helper\EventLogs;

use App\Models\Admin;
use App\Models\Videos;
use App\Models\VideoComments;
use App\Models\VideoCategories;
use App\Helper\GenerateSlug;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class videoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $res1=Videos::select('id', 'title', 'slug', 'short_body', 'category_id', 'category_title', 'views',
        'rating', 'comments_number', 'image_url','status', 'created_at')
            ->where('title', 'like', '%' . $request->q . '%');

        if($request->category){
            $res1->where('category_title', 'like', '%' . $request->category . '%');
        }

        if($request->tags){
            $res1->where('tags', 'like', '%' . $request->tags . '%');
        }

        $videos=$res1->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $videos
        ], Response::HTTP_OK);
    }

    public function getPopularVideos(){
        $videos=Videos::select('id', 'title', 'slug', 'category_id', 'category_title', 'views',
        'rating', 'comments_number', 'image_url', 'created_at')
            ->orderBy("views","desc")->take(5)->get();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $videos
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
            'title' => 'required|string|max:255|unique:videos',
            'short_body' => 'required|string',
            'long_body' => 'required|string',
            'category_id' => 'required',
            'category_title' => 'required',
            'image_url' => 'required|string',
            'video_url' => 'required|string',
            'status' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $videoCategories = VideoCategories::where("id",$request->category_id);

        if(!$videoCategories->exists()){
            return response()->json([
                'success' => false,
                'statusCode' => 201,
                'message' => [
                    'title'=> 'دسته بندی یافت نشد.'
                ],
            ], Response::HTTP_OK);
        }

        $video = Videos::create([
            'title' => $request->title,
            'slug' => GenerateSlug::get($request->title),
            'short_body' => $request->short_body,
            'long_body' => $request->long_body,
            'category_id' => $request->category_id,
            'category_title' => $request->category_title,
            'image_url' => $request->image_url,
            'video_url' => $request->video_url,
            'status' => $request->status,
            'tags'=> $request->tags
        ]);

        $videoCategories->first()->increment('count',1);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
               EventLogs::addToLog([
                    'subject' => "افزودن ویدیو آموزشی جدید",
            	    'body' => $video,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $video
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Videos $video)
    {
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $video
        ], Response::HTTP_OK);
    }

    public function seen_news(Request $request)
    {
        Videos::where("id",$request->id)->increment('views',1);;

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
    public function update(Request $request, Videos $video)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'short_body' => 'required|string',
            'long_body' => 'required|string',
            'category_id' => 'required',
            'category_title' => 'required',
            'image_url' => 'required|string',
            'video_url' => 'required|string',
            'status' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if($request->title != $video->title){
            if(Videos::where("title",$request->title)->exists()){
                return response()->json([
                    'success' => false,
                    'statusCode' => 201,
                    'message' => [
                        'title'=> 'ویدیو یا این عنوان قبلأ ثبت شده است.'
                    ],
                ], Response::HTTP_OK);
            }
        }

        if($request->category_id != $video->category_id){
            if(!VideoCategories::where("id",$request->category_id)->exists()){
                return response()->json([
                    'success' => false,
                    'statusCode' => 201,
                    'message' => 'دسته بندی یافت نشد.',
                ], Response::HTTP_OK);
            }
        }

        $video->update([
            'title' => $request->title,
            'slug' => GenerateSlug::get($request->title),
            'short_body' => $request->short_body,
            'long_body' => $request->long_body,
            'category_id' => $request->category_id,
            'category_title' => $request->category_title,
            'image_url' => $request->image_url,
            'video_url' => $request->video_url,
            'status' => $request->status,
            'tags'=> $request->tags
        ]);



        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "ویرایش ویدیو آموزشی",
            	    'body' => $video,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $video
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,Videos $video)
    {
        $video->delete();
        VideoCategories::where("id",$video->category_id)->first()->decrement('count',1);

        $videoComments = VideoComments::where("video_id",$video->id);
        if($videoComments->exists()){
            return $videoComments->delete();
        }

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
               EventLogs::addToLog([
                    'subject' => "حذف ویدیو آموزشی",
            	    'body' => $video,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $video
        ], Response::HTTP_OK);
    }
}
