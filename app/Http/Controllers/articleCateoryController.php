<?php

namespace App\Http\Controllers;

use App\Helper\EventLogs;

use App\Models\Admin;
use App\Models\Article;
use App\Models\ArticleCategories;
use App\Helper\GenerateSlug;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class articleCateoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->page){
            $articleCategories=ArticleCategories::where('title', 'like', '%' . $request->q . '%')->paginate(10);
        }else{
            $articleCategories=ArticleCategories::all();
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $articleCategories
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
            'title' => 'required|string|max:255|unique:article_categories',
            'order' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        $articleCategories = ArticleCategories::create([
            'title' => $request->title,
            'order' => $request->order,
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
                EventLogs::addToLog([
                    'subject' => "افزودن دسته بندی جدید برای مقاله‌ها",
            	    'body' => $articleCategories,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $articleCategories
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ArticleCategories $articleCategory)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'order' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if($request->title != $articleCategory->title){
            if(ArticleCategories::where("title",$request->title)->exists()){
                return response()->json([
                    'success' => false,
                    'statusCode' => 201,
                    'message' => [
                        'title'=> 'دسته بندی با این عنوان قبلأ ثبت شده است.',
                    ]
                ], Response::HTTP_OK);
            }
        }

        $articleCategory->update([
            'title' => $request->title,
            'order' => $request->order,
        ]);

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
               EventLogs::addToLog([
                    'subject' => "ویرایش دسته‌بندی مقاله",
            	    'body' => $articleCategory,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $articleCategory
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,ArticleCategories $articleCategory)
    {
        if(Article::where("category_id",$articleCategory->id)->exists()){
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' =>  [
                    'title'=> 'به دلیل وابستگی با سایر بخش ها امکان حذف وجود ندارد.'
                 ],
            ], Response::HTTP_OK);
        }

        $articleCategory->delete();

        if($request->header('agent')){
            $admin=Admin::where("id",$request->header('agent'))->first();

            if($admin){
               EventLogs::addToLog([
                    'subject' => "حذف دسته‌بندی مقاله",
            	    'body' => $articleCategory,
            	    'user_id' => $admin->id,
            	    'user_name'=> $admin->first_name." ".$admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $articleCategory
        ], Response::HTTP_OK);
    }
}
