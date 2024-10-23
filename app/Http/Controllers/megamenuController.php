<?php

namespace App\Http\Controllers;

use App\Models\Megamenu;
use App\Models\ShopInfo;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class megamenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $megamanu = Megamenu::all();

        return response()->json([
                'success' => true,
                'statusCode' => 201,
                'message' => 'عملیات با موفقیت انجام شد.',
                "data" => $megamanu
            ], Response::HTTP_OK);
    }

    public function updateMegaMenu(Request $request)
    {
        $shopInfo=ShopInfo::where("id",1)->first();

        $validator = Validator::make($request->all(), [
            'mega_menu' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $shopInfo->update([
            'mega_menu' => $request->mega_menu,
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    public function getMegaMenu()
    {
        $mega_menu=ShopInfo::select("mega_menu")->where("id",1)->first();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => json_decode($mega_menu->mega_menu, true)
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
            'level' => 'required|numeric',
            'image' => 'nullable',
            'link' => 'nullable',
            'parent_id' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()->toJson()
            ], Response::HTTP_OK);
        }
    }
}
