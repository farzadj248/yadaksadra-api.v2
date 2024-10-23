<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketCategories;
use App\Helper\GenerateSlug;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class ticketCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->page){
            $ticketCategory=TicketCategories::where('title', 'like', '%' . $request->q . '%')->paginate(10);
        }else{
            $ticketCategory=TicketCategories::all();
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $ticketCategory
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
            'title' => 'required|string|max:255|unique:ticket_categories',
            'order' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $ticketCategories = TicketCategories::create([
            'title' => $request->title,
            'order' => $request->order,
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $ticketCategories
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TicketCategories $ticketCategory)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'order' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if($request->title != $ticketCategory->title){
            if(TicketCategories::where("title",$request->title)->exists()){
                return response()->json([
                    'success' => false,
                    'statusCode' => 201,
                    'message' => [
                        'title' => 'دسته بندی با این عنوان قبلأ ثبت شده است.'
                    ],
                ], Response::HTTP_OK);
            }
        }

        $ticketCategory->update([
            'title' => $request->title,
            'order' => $request->order,
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $ticketCategory
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(TicketCategories $ticketCategory)
    {
        if(Ticket::where("category_id",$ticketCategory->id)->exists()){
            return response()->json([
                'success' => false,
                'statusCode' => 201,
                'message' => 'به دلیل وابستگی با سایر بخش ها امکان حذف وجود ندارد.'
            ], Response::HTTP_OK);
        }

        $ticketCategory->delete();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $ticketCategory
        ], Response::HTTP_OK);
    }
}
