<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommonResources;
use App\Models\Banners;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class bannerscontroller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = Banners::where('title', 'like', '%' . $request->q . '%')
            ->where('type','!=', 'slider_banner')
            ->paginate(10);
        return CommonResources::collection($data);
    }

    public function getSliders(Request $request)
    {
        $data=Banners::where('title', 'like', '%' . $request->q . '%')
        ->where('type', 'slider_banner')
        ->paginate(10);
        return CommonResources::collection($data);
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
            'title' => 'required|string|max:255',
            'image_url' => 'nullable|string|required_without:video_url',
            'video_url' => 'nullable|string|required_without:image_url',
            'start_date' => 'nullable|date_format:Y-m-d H:i:s',
            'expire_date' => 'nullable|date_format:Y-m-d H:i:s|after:start_date',
            'type' => 'required|in:slider_banner,bottom_banner_1,bottom_banner_2,bottom_banner_3,bottom_banner_4,bottom_banner_5,top_header_banner',
            'order' => 'required|numeric',
            'user_type' => 'required|numeric',
            'status' => 'required|boolean',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $banner = Banners::create([
            'title' => $request->title,
            'image_url' => $request->image_url ?? '',
            'image_link' => $request->image_link,
            'video_url' => $request->video_url ?? '',
            'start_date' => $request->start_date,
            'expire_date' => $request->expire_date,
            'type' => $request->type,
            'user_type' => $request->user_type,
            'order' => $request->order,
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $banner
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Banners $banner)
    {
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $banner
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Banners $banner)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image_url' => 'nullable|string|required_without:video_url',
            'video_url' => 'nullable|string|required_without:image_url',
            'image_link' => 'required|string',
            'start_date' => 'nullable|date_format:Y-m-d H:i:s',
            'expire_date' => 'nullable|date_format:Y-m-d H:i:s|after:start_date',
            'user_type' => 'required|numeric',
            'status' => 'required|boolean',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $banner->update([
            'title' => $request->title,
            'image_url' => $request->image_url ?? '',
            'video_url' => $request->video_url ?? '',
            'image_link' => $request->image_link,
            'start_date' => $request->start_date,
            'expire_date' => $request->expire_date,
            'user_type' => $request->user_type,
            'order' => $request->order,
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $banner
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Banners $banner)
    {
        $banner->delete();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $banner
        ], Response::HTTP_OK);
    }
}
