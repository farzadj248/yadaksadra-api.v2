<?php

namespace App\Http\Controllers;

use App\Models\usersMedia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;

class usersMediaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $medias=usersMedia::where("user_id",$user->id)->get();

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
        $response1 = explode(' ', $request->header('Authorization'));
        $token = trim($response1[1]);
        $user = JWTAuth::authenticate($token);

        $validator = Validator::make($request->all(), [
            'file' => 'required',
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

        if(!Storage::disk('ftp')->exists($user->personnel_code)){
            Storage::disk('ftp')->makeDirectory($user->personnel_code);
        }

        $isOk = Storage::disk('ftp')->put('users_media/'.$user->personnel_code.'/'.'/'.$filenametostore, fopen($request->file, 'r+'));

        if($isOk){
            $media = usersMedia::create([
                'user_id'=> $user->id,
                'title' => $filenametostore,
                'type' => $request->type,
                'size' => $request->size,
                'url' => 'https://dl.yadaksadra.com/storage/users_media/'.$user->personnel_code.'/'.$filenametostore,
                'public_path' => 'users_media/'.$user->personnel_code.'/'.$filenametostore
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(usersMedia $usersMedia)
    {
        if(Storage::disk('ftp')->exists($usersMedia->public_path)){
            Storage::disk('ftp')->delete($usersMedia->public_path);
            $usersMedia->delete();

            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'عملیات با موفقیت انجام شد.',
                'data' => $usersMedia
            ], Response::HTTP_OK);

        }

        return response()->json([
            'success' => false,
            'statusCode' => 422,
            'message' => 'عملیات با خطا مواجه شد.',
        ], Response::HTTP_OK);
    }
}
