<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use App\Models\ViewersStatistics;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class ViewersStatisticsController extends Controller
{
    public function getAll(Request $request)
    {
        $all_clicks=ViewersStatistics::leftJoin('products', function ($query) {
            $query->on('products.id', '=', 'viewers_statistics.post_id');
        })
        ->select('products.title','products.slug','viewers_statistics.ip_address as ip','viewers_statistics.price','viewers_statistics.action','viewers_statistics.created_at')
        ->where("viewers_statistics.type","product")
        ->where("viewers_statistics.action","seen")
        ->orderBy("viewers_statistics.id","desc")
        ->paginate(10);
        
        $count_clicks = DB::table('viewers_statistics')
        ->select('products.slug', 'products.title', DB::raw('count(*) as view_count'))
        ->leftJoin('products', 'viewers_statistics.post_id', '=', 'products.id')
        ->groupBy('products.slug', 'products.title')
        ->where("viewers_statistics.type","product")
        ->where("viewers_statistics.action","seen")
        ->paginate(10);
        
        if($request->filled("start_date")){
            $startDate=$request->start_date;
        }else{
           $startDate = Carbon::today();  
        }
        
        if($request->filled("end_date")){
            $endDate=$request->end_date;
        }else{
           $endDate = Carbon::tomorrow();  
        }

        $statistics = ViewersStatistics::select(DB::raw('DATE(viewers_statistics.created_at) as date'), 'products.title', DB::raw('COUNT(*) as view_count')) 
        ->join('products', 'viewers_statistics.post_id', '=', 'products.id') 
        ->whereBetween('viewers_statistics.created_at', [$startDate, $endDate]) 
        ->groupBy('date', 'products.title')
        ->orderBy('date', 'asc') 
        ->where("viewers_statistics.type","product")
        ->where("viewers_statistics.action","seen")
        ->paginate(10);
        
        $buy_statistics = ViewersStatistics::select(DB::raw('DATE(viewers_statistics.created_at) as date'), 'products.title', DB::raw('COUNT(*) as buy_count')) 
        ->join('products', 'viewers_statistics.post_id', '=', 'products.id') 
        ->whereBetween('viewers_statistics.created_at', [$startDate, $endDate]) 
        ->groupBy('date', 'products.title')
        ->orderBy('date', 'asc') 
        ->where("viewers_statistics.type","product")
        ->where("viewers_statistics.action","buy")
        ->paginate(10);

 
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'clicks'=> $all_clicks,
                'sum_clicks'=> $count_clicks,
                'statistics'=> $statistics,
                'buy_statistics'=> $buy_statistics
            ]
        ], Response::HTTP_OK);
    }
}
