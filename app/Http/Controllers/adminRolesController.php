<?php

namespace App\Http\Controllers;

use App\Helper\EventLogs;
use App\Http\Resources\CommonResources;
use App\Models\Admin;
use App\Models\AdminRoles;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class adminRolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $adminRoles=AdminRoles::select("id","en_title","fa_title","access_leveles")
        ->whereRaw('concat(en_title,fa_title) like ?', "%{$request->q}%")
        ->paginate(10);
        return CommonResources::collection($adminRoles);
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
            'en_title' => 'required|string|max:255|unique:admin_roles',
            'fa_title' => 'required|string|max:255|unique:admin_roles',
            'permissions.*' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $adminRole = AdminRoles::create([
            'en_title' => $request->en_title,
            'fa_title' => $request->fa_title,
            'access_leveles' => $request->permissions
        ]);

        $admin=auth()->guard('admin')->user();
        EventLogs::addToLog([
            'subject' =>"ایجاد نقش جدید:". $request->fa_title.' توسط '. $admin->first_name.' '. $admin->last_name,
            'body' => $adminRole,
            'user_id' => $admin->id,
            'user_name'=> $admin->first_name." ".$admin->last_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(AdminRoles $adminRole)
    {
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $adminRole
        ], Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AdminRoles $adminRole)
    {
        $validator = Validator::make($request->all(), [
            'en_title' => 'required|string|max:255',
            'fa_title' => 'required|string|max:255',
            'permissions.*' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if(AdminRoles::where("en_title",$request->en_title)->get()->count()>1){
            return response()->json([
                'success' => false,
                'statusCode' => 201,
                'message' => 'نقش با این عنوان قبلأ ثبت شده است.',
            ], Response::HTTP_OK);
        }

        $admin = auth()->guard('admin')->user();
        EventLogs::addToLog([
            'subject' => "ویرایش نقش: " . $adminRole->fa_title . ' توسط ' . $admin->first_name . ' ' . $admin->last_name.' به '.$request->fa_title,
            'body' => $adminRole,
            'user_id' => $admin->id,
            'user_name' => $admin->first_name . " " . $admin->last_name,
        ]);

        $adminRole->update([
            'en_title' => $request->en_title,
            'fa_title' => $request->fa_title,
            'access_leveles' => $request->permissions
        ]);

        return response()->json([
            'success' => true,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,AdminRoles $adminRole)
    {
        if(Admin::where('role_id',$adminRole->id)->exists()){
            return response()->json([
                'success' => false,
                'message' => 'به دلیل وابستگی با سایر بخش ها امکان حذف وجود ندارد.'
            ], 403);
        }

        $adminRole->delete();

        $admin=auth()->guard('admin')->user();
        EventLogs::addToLog([
            'subject' => "حذف نقش ". $adminRole->fa_title.' توسط '. $admin->first_name.' '. $admin->last_name,
            'body' => $adminRole,
            'user_id' => $admin->id,
            'user_name' => $admin->first_name . " " . $admin->last_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }
}
