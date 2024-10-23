<?php

namespace App\Http\Controllers;

use App\Models\Faqs;
use App\Models\FaqsCategories;
use App\Helper\GenerateSlug;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class faqsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->page){
            $faqs=Faqs::where('title', 'like', '%' . $request->q . '%')->paginate(10);
        }else{
            $faqs=Faqs::all();
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $faqs
        ], Response::HTTP_OK);
    }

    public function webFaqs(Request $request)
    {
        $categories = FaqsCategories::all();

        $faqs = $categories->map(function($category, $key) {
            return [
                'title' => $category->title,
                'items' => Faqs::select("title","body")->where('category_id', $category->id)->get()
            ];
        });

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $faqs
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
            'body' => 'required|string',
            'category_id' => 'required|numeric',
            'category_title' => 'required|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $faqsCategories=FaqsCategories::where("id",$request->category_id)->exists();

        if(!$faqsCategories){
            return response()->json([
                'success' => false,
                'statusCode' => 201,
                'message' => 'دسته بندی یافت نشد.',
            ], Response::HTTP_OK);
        }

        $faqs = Faqs::create([
            'title' => $request->title,
            'body' => $request->body,
            'category_id' => $request->category_id,
            'category_title' => $request->category_title
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $faqs
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,Faqs $faq)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'category_id' => 'required|numeric',
            'category_title' => 'required|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if($request->category_id != $faq->category_id){
            if(!FaqsCategories::where("id",$request->category_id)->exists()){
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => [
                        'title' => 'دسته بندی یافت نشد.'
                    ],
                ], Response::HTTP_OK);
            }
        }

        $faq->update([
            'title' => $request->title,
            'body' => $request->body,
            'category_id' => $request->category_id,
            'category_title' => $request->category_title
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $faq
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Faqs $faq)
    {
        $faq->delete();
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $faq
        ], Response::HTTP_OK);
    }
}
