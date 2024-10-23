<?php

namespace App\Http\Controllers;

use App\Models\Faqs;
use App\Models\FaqsCategories;
use App\Helper\GenerateSlug;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class faqsCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $faqsCategories=FaqsCategories::all();
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $faqsCategories
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
            'title' => 'required|string|max:255|unique:faqs_categories',
            'order' => 'required|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()->toJson()
            ], Response::HTTP_OK);
        }

        $faqsCategories = FaqsCategories::create([
            'title' => $request->title,
            'order' => $request->order,
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $faqsCategories
        ], Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FaqsCategories $faqsCategory)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'order' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()->toJson()
            ], Response::HTTP_OK);
        }

        if($request->title != $faqsCategory->title){
            if(!FaqsCategories::where("title",$request->title)->exists()){
                return response()->json([
                    'success' => false,
                    'statusCode' => 201,
                    'message' => 'دسته بندی با این عنوان قبلأ ثبت شده است.',
                ], Response::HTTP_OK);
            }
        }

        $faqsCategory->update([
            'title' => $request->title,
            'order' => $request->order
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $faqsCategory
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(FaqsCategories $faqsCategory)
    {
        if(Faqs::where("category_id",$faqsCategory->id)->exists()){
            return response()->json([
                'success' => false,
                'statusCode' => 201,
                'message' => 'به دلیل وابستگی با سایر بخش ها امکان حذف وجود ندارد.',
            ], Response::HTTP_OK);
        }

        $faqsCategory->delete();

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $faqsCategory
        ], Response::HTTP_OK);
    }
}
