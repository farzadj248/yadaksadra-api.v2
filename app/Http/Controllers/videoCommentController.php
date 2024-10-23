<?php

namespace App\Http\Controllers;

use App\Helper\EventLogs;

use App\Models\Admin;
use App\Models\VideoComments;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class videoCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $videoComments=VideoComments::leftJoin('users', function ($query) {
            $query->on('users.id', '=', 'video_comments.user_id');
        })
        ->leftJoin('videos', function ($query) {
            $query->on('videos.id', '=', 'video_comments.video_id');
        })
            ->select('video_comments.*','users.first_name','users.last_name',
            'videos.title as video_title')
            ->where('video_comments.body', 'like', '%' . $request->q . '%')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $videoComments
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
            'video_id' => 'required|numeric',
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

        $comments = VideoComments::create([
            'reply_id'=> $request->reply_id,
            'video_id'=>$request->video_id,
            'user_id'=>$request->user_id,
            'body'=> $request->body
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $comments
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VideoComments $videoComment)
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

        $videoComment->update([
            'status'=>$request->status,
            'rejected_reason' => $request->status==3?$request->rejected_reason:null
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
               EventLogs::addToLog([
                    'subject' => "تغییر وضعیت نظر کاربر در رابطه با ویدیو آموزشی",
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
            'data' => $newsComment
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(VideoComments $videoComment)
    {
        $videoComment->delete();
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $videoComment
        ], Response::HTTP_OK);
    }
}
