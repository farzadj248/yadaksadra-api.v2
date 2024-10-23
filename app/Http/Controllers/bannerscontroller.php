<?php

namespace App\Http\Controllers;

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
        $horizontalBannerSlider = Banners::where('type',2)->orderBy("order","ASC")->get();
        $horizontalBannerHeader = Banners::where('type',3)->where("status",1)->first();
        $horizontalBanner = Banners::where('type',4)->orderBy("order","ASC")->get();
        $horizontalVideo = Banners::where('type',5)->first();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                "HorizontalBannerHeader"=> $horizontalBannerHeader,
                "HorizontalBannerSlider"=> $horizontalBannerSlider,
                "HorizontalBanner"=> $horizontalBanner,
                "HorizontalVideo" => $horizontalVideo
            ]
        ], Response::HTTP_OK);
    }

    public function getSliders(Request $request)
    {
        $banners=Banners::where('title', 'like', '%' . $request->q . '%')
        ->where('type',1)
        ->paginate(10);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $banners
        ], Response::HTTP_OK);
    }

    public function updateHorizontalBannerHeader(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image_url' => 'required|string',
            'thumbnail' => 'required|string',
            'image_link' => 'nullable|string',
            'order' => 'required|numeric',
            'status' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if($request->id){
            $banner = Banners::where("id",$request->id)->first()->update([
                "title"=> $request->title,
                "image_url"=> $request->image_url,
                'thumbnail'=> $request->thumbnail,
                'image_link'=> $request->image_link,
                'order'=> $request->order,
                'status'=> $request->status
            ]);
        }else{
            $adv_banner = Banners::where("type",3)->first();
            if($adv_banner){
                $banner = $adv_banner->update([
                    "title"=> $request->title,
                    "image_url"=> $request->image_url,
                    'thumbnail'=> $request->thumbnail,
                    'image_link'=> $request->image_link,
                    'order'=> $request->order,
                    'status'=> $request->status
                ]);
            }else{
               $banner = Banners::create([
                    "title"=> $request->title,
                    "image_url"=> $request->image_url,
                    'thumbnail'=> $request->thumbnail,
                    'image_link'=> $request->image_link,
                    'order'=> $request->order,
                    'status'=> $request->status,
                    'type'=> 3
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $banner
        ], Response::HTTP_OK);
    }

    public function updateHorizontalVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image_url' => 'required|string',
            'video_url' => 'required|string',
            'status' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if($request->id){
            $banner = Banners::where("id",$request->id)->first()->update([
                "title"=> $request->title,
                "image_url"=> $request->image_url,
                'video_url'=> $request->video_url,
                'status'=> $request->status
            ]);
        }else{
            $adv_banner = Banners::where("type",5)->first();
            if($adv_banner){
                $banner = $adv_banner->update([
                    "title"=> $request->title,
                    "image_url"=> $request->image_url,
                    'video_url'=> $request->video_url,
                    'status'=> $request->status
                ]);
            }else{
               $banner = Banners::create([
                    "title"=> $request->title,
                    "image_url"=> $request->image_url,
                    'video_url'=> $request->video_url,
                    'status'=> $request->status,
                    'type'=> 5
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $banner
        ], Response::HTTP_OK);
    }

    public function updateHorizontalBannerSlider(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image_url' => 'required|string',
            'image_link' => 'nullable|string',
            'order' => 'required|numeric',
            'status' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if($request->id){
            $banner = Banners::where("id",$request->id)->first()->update([
                "title"=> $request->title,
                "image_url"=> $request->image_url,
                'image_link'=> $request->image_link,
                'order'=> $request->order,
                'status'=> $request->status
            ]);
        }else{
            $banner = Banners::create([
                "title"=> $request->title,
                "image_url"=> $request->image_url,
                'image_link'=> $request->image_link,
                'order'=> $request->order,
                'status'=> $request->status,
                'type'=> 2
            ]);
        }

        $horizontalBannerSlider = Banners::where('type',2)->orderBy("order","ASC")->get();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $horizontalBannerSlider
        ], Response::HTTP_OK);
    }

    public function updateHorizontalBanner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'title' => 'required|string|max:255',
            'image_url' => 'required|string',
            'image_link' => 'nullable|string',
            'order' => 'required|numeric',
            'status' => 'required|numeric',
            'type' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $banner = Banners::where("id",$request->id)->first()->update([
            "title"=> $request->title,
            "image_url"=> $request->image_url,
            'image_link'=> $request->image_link,
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $horizontalBanner
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
            'title' => 'required|string|max:255',
            'image_url' => 'required|string',
            'thumbnail'=> 'nullable|string',
            'start_date' => 'required|string|max:20',
            'expire_date' => 'required|string|max:20',
            'type' => 'required|numeric',
            'order' => 'required|numeric',
            'user_type' => 'required|numeric',
            'status' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()->toJson()
            ], Response::HTTP_OK);
        }

        $banner = Banners::create([
            'title' => $request->title,
            'image_url' => $request->image_url,
            'thumbnail' => $request->thumbnail,
            'image_link' => $request->image_link,
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
            'image_url' => 'required|string',
            'thumbnail'=> 'nullable|string',
            'image_link' => 'required|string',
            'start_date' => 'required|string|max:20',
            'expire_date' => 'required|string|max:20',
            'type' => 'required|numeric',
            'user_type' => 'required|numeric',
            'status' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()->toJson()
            ], Response::HTTP_OK);
        }

        $banner->update([
            'title' => $request->title,
            'image_url' => $request->image_url,
            'thumbnail' => $request->thumbnail,
            'image_link' => $request->image_link,
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
