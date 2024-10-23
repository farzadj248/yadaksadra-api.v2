<?php

namespace App\Http\Controllers;

use App\Helper\EventLogs;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class eventsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll(Request $request)
    {
        $logs=EventLogs::logActivityLists($request->search);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $logs
        ], Response::HTTP_OK);
    }
    
    public function get(Request $request)
    {
        $logs=EventLogs::get($request->file);
        
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $logs
        ], Response::HTTP_OK);
    }

    public function remove(Request $request)
    {
        EventLogs::remove($request->file);
        
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }
}
