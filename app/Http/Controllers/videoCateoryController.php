<?php

namespace App\Http\Controllers;

use App\Helper\EventLogs;

use App\Models\Admin;
use App\Models\Videos;
use App\Models\VideoCategories;
use App\Helper\GenerateSlug;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class videoCateoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->page){
            $videoCategories=VideoCategories::where('title', 'like', '%' . $request->q . '%')->paginate(10);
        }else{
            $videoCategories=VideoCategories::all();
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $videoCategories
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
            'title' => 'required|string|max:255|unique:video_categories',
            'order' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()->toJson()
            ], Response::HTTP_OK);
        }

        $videoCategories = VideoCategories::create([
            'title' => $request->title,
            'order' => $request->order,
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
               EventLogs::addToLog([
                    'subject' => "افزودن دسته بندی جدید برای ویدیوهای آموزشی",
            	    'body' => $videoCategory,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $videoCategories
        ], Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VideoCategories $videoCategory)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'order' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()->toJson()
            ], Response::HTTP_OK);
        }

        if($request->title != $videoCategory->title){
            if(VideoCategories::where("title",$request->title)->exists()){
                return response()->json([
                    'success' => false,
                    'statusCode' => 201,
                    'message' => [
                        'title'=> 'دسته بندی با این عنوان قبلأ ثبت شده است.',
                    ]
                ], Response::HTTP_OK);
            }
        }

        $videoCategory->update([
            'title' => $request->title,
            'order' => $request->order,
        ]);



        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
               EventLogs::addToLog([
                    'subject' => "ویرایش دسته بندی ویدیوآموزشی",
            	    'body' => $videoCategory,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $videoCategory
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,VideoCategories $videoCategory)
    {
        if(Videos::where("category_id",$videoCategory->id)->exists()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => [
                   'title'=> 'به دلیل وابستگی با سایر بخش ها امکان حذف وجود ندارد.'
                ],
            ], Response::HTTP_OK);
        }

        $videoCategory->delete();

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
               EventLogs::addToLog([
                    'subject' => "حذف دسته بندی ویدیو آموزشی",
            	    'body' => $videoCategory,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $videoCategory
        ], Response::HTTP_OK);
    }
}
