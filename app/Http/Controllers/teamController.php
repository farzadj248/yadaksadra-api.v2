<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Admin;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Helper\EventLogs;
use Illuminate\Http\Request;

class teamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->page){
            $data=Team::where('full_name', 'like', '%' . $request->q . '%')
            ->select("id","avatar","full_name","job_position","body")
            ->paginate(10);
        }else{
            $data=Team::select("id","avatar","full_name","job_position","body")
            ->orderBy("sort","asc")
            ->get();
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $data
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
            'full_name' => 'required|string|max:255',
            'avatar' => 'nullable|string',
            'job_position' => 'required|string',
            'body' => 'nullable|string',
            'sort' => 'nullable|numeric'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $team = Team::create([
            'full_name' => $request->full_name,
            'avatar'=> $request->avatar,
            'job_position'=> $request->job_position,
            'body' => $request->body,
            'sort' => $request->sort
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "افزودن عضو جدید به شرکت",
            	    'body' => $team,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $team
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function show(Team $team)
    {
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $team
        ], Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Team $team)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'avatar' => 'nullable|string',
            'job_position' => 'required|string',
            'body' => 'nullable|string',
            'sort' => 'nullable|numeric'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $team->update([
            'full_name' => $request->full_name,
            'avatar'=> $request->avatar,
            'job_position'=> $request->job_position,
            'body' => $request->body,
            'sort' => $request->sort
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "ویرایش اطلاعات اعضای شرکت - ".$request->full_name,
            	    'body' => $team,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $team
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function destroy(Team $team)
    {
        if($team){
            $team->delete();

            return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'عملیات با موفقیت انجام شد.',
                'data' => $team
            ], Response::HTTP_OK);
        }
    }
}
