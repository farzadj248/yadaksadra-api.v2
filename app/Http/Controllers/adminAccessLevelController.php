<?php

namespace App\Http\Controllers;

use App\Models\AdminAccessLevels;
use App\Models\AdminRoles;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class adminAccessLevelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $accessLevels=AdminAccessLevels::select("id","en_title","fa_title")
        ->orderBy("order","asc")
        ->get();
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $accessLevels
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
            'en_title' => 'required|string|max:255|unique:admin_access_levels',
            'fa_title' => 'required|string|max:255|unique:admin_access_levels'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()->toJson()
            ], Response::HTTP_OK);
        }

        $adminAccessLevels = AdminAccessLevels::create([
            'en_title' => $request->en_title,
            'fa_title' => $request->fa_title
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $adminAccessLevels
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,AdminAccessLevels $adminAccessLevel)
    {
        $validator = Validator::make($request->all(), [
            'en_title' => 'required|string|max:255',
            'fa_title' => 'required|string|max:255'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()->toJson()
            ], Response::HTTP_OK);
        }

        $adminAccessLevels->update([
            'en_title' => $request->en_title,
            'fa_title' => $request->fa_title
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $adminAccessLevels
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(AdminAccessLevels $adminAccessLevel)
    {
        if(AdminRoles::where('admin_roles.access_leveles', 'LIKE', '%' .$adminAccessLevel->en_title. '%')->exists()){
            return response()->json([
                'success' => false,
                'statusCode' => 201,
                'message' => 'به دلیل وابستگی با سایر بخش ها امکان حذف وجود ندارد.',
            ], Response::HTTP_OK);
        }

        $adminAccessLevel->delete();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $adminAccessLevel
        ], Response::HTTP_OK);
    }
}
