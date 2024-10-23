<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required',
            'pageSize' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        switch ($request->q) {
            case 'all':
                $q="";
                break;

            case 'image':
                $q="image";
                break;

            case 'video':
                $q="video";
                break;

            case 'audio':
                $q="audio";
                break;

            default:
                $q="*";
                break;
        }

        $medias=Media::where('media.type', 'like', '%' . $q . '%')
        ->orderBy("id","desc")
        ->take($request->pageSize)->get();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $medias
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
            'file' => 'required',
            'title' => 'required',
            'size' => 'required',
            'type' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $filenamewithextension = $request->file->getClientOriginalName();
        $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);
        $extension = $request->file->getClientOriginalExtension();
        $filenametostore = uniqid().'.'.$extension;

        $date = date('Y-m-d', time());

        if(!Storage::disk('ftp')->exists($date)){
            Storage::disk('ftp')->makeDirectory($date);
        }

        $isOk = Storage::disk('ftp')->put($date.'/'.$filenametostore, fopen($request->file, 'r+'));


        if($isOk){
            $media = Media::create([
                'title' => $request->title,
                'type' => $request->type,
                'size' => $request->size,
                'url' => 'https://dl.yadaksadra.com/storage/'.$date.'/'.$filenametostore,
                'public_path' => $date.'/'.$filenametostore
            ]);

            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'عملیات با موفقیت انجام شد.',
                'data' => $media
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => false,
            'statusCode' => 422,
            'message' => 'عملیات با خطا مواجه شد.',
            'data' => $media
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Media $media)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $media->update([
            'title' => $request->title,
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $media
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Media $media)
    {
        if(Storage::disk('ftp')->exists($media->public_path)){
            Storage::disk('ftp')->delete($media->public_path);
            $media->delete();

            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'عملیات با موفقیت انجام شد.',
                'data' => $media
            ], Response::HTTP_OK);

        }

        return response()->json([
            'success' => false,
            'statusCode' => 422,
            'message' => 'عملیات با خطا مواجه شد.',
        ], Response::HTTP_OK);
    }
}
