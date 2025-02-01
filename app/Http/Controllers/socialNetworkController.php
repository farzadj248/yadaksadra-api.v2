<?php

namespace App\Http\Controllers;

use App\Models\SocialNetwork;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class socialNetworkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->page) {
            $socialNetwork = SocialNetwork::where('fa_title', 'like', '%' . $request->q . '%')
                ->orWhere('en_title', 'like', '%' . $request->q . '%')
                ->paginate(10);
        } else {
            $socialNetwork = SocialNetwork::all();
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $socialNetwork
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
            'en_title' => 'required|string|max:255|unique:social_networks',
            'fa_title' => 'required|string|max:255|unique:social_networks',
            'icon' => 'required|string',
            'link' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()->toJson()
            ], Response::HTTP_OK);
        }

        $socialNetwork = SocialNetwork::create([
            'en_title' => $request->en_title,
            'fa_title' => $request->fa_title,
            'icon' => $request->icon,
            'link' => $request->link,
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $socialNetwork
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SocialNetwork $socialNetwork)
    {
        $validator = Validator::make($request->all(), [
            'en_title' => 'required|string|max:255',
            'fa_title' => 'required|string|max:255',
            'icon' => 'required|string',
            'link' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $socialNetwork->update([
            'en_title' => $request->en_title,
            'fa_title' => $request->fa_title,
            'icon' => $request->icon,
            'link' => $request->link,
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $socialNetwork
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(SocialNetwork $socialNetwork)
    {
        $socialNetwork->delete();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $socialNetwork
        ], Response::HTTP_OK);
    }
}
