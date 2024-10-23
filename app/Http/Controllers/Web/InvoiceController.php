<?php

namespace App\Http\Controllers\Web;

use App\Models\Cart;
use App\Models\User;
use App\Models\Orders;
use App\Models\Transactions;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class InvoiceController extends Controller
{
    public function generatePDF(Request $request)
    {
        $order = Orders::where("order_code",$request->id)
        ->whereIn("status",[2,3,4,5])
        ->first();
        
        if(!$order) return abort(404);

        $orderItems = Cart::where("order_id",$order->id)->leftJoin('products', function ($query) {
            $query->on('products.id', '=', 'carts.product_id');
        })
        ->leftJoin('products_images', function ($query) {
            $query->on('products_images.product_id', '=', 'carts.product_id');
        })
        ->select('carts.*','products.slug','products.title','products_images.url')
        ->get();

        $transactions = Transactions::where("order_id",$order->order_code)->first();

        $user = User::where("id",$order->user_id)->first();

        if($order->isRejected == 0){
            $now = Carbon::now();
            $created_at=$now->toDateTimeString();

            $formatted_dt1=Carbon::parse($order->created_at);

            $formatted_dt2=Carbon::parse($created_at);

            $date_diff=$formatted_dt1->diffInDays($formatted_dt2);

            if($date_diff>=7){
               $order->update([
                    "isRejected"=> 4
                ]);
            }
        }

        $data = [
            'title' => 'فروشگاه اینترنتی یدک صدرا',
            'order_code' => $order->order_code,
            'order' => $order,
            'address'=> json_decode($order->address, true),
            'user' => $user,
            'orderItems'=> $orderItems,
            'transactions'=> $transactions,
            'isPost'=> false
        ];

        $pdf = Pdf::loadView('invoice', compact('data'));
        return $pdf->stream('invoice-'.$order->order_code.'.pdf');
    }
    
    public function pdfForPost(Request $request)
    {
        $order = Orders::where("order_code",$request->id)
        ->whereIn("status",[2,3,4,5])
        ->first();
        
        if(!$order) return abort(404);

        $orderItems = Cart::where("order_id",$order->id)->leftJoin('products', function ($query) {
            $query->on('products.id', '=', 'carts.product_id');
        })
        ->leftJoin('products_images', function ($query) {
            $query->on('products_images.product_id', '=', 'carts.product_id');
        })
        ->select('carts.*','products.slug','products.title','products_images.url')
        ->get();

        $transactions = Transactions::where("order_id",$order->order_code)->first();

        $user = User::where("id",$order->user_id)->first();

        if($order->isRejected == 0){
            $now = Carbon::now();
            $created_at=$now->toDateTimeString();

            $formatted_dt1=Carbon::parse($order->created_at);

            $formatted_dt2=Carbon::parse($created_at);

            $date_diff=$formatted_dt1->diffInDays($formatted_dt2);

            if($date_diff>=7){
               $order->update([
                    "isRejected"=> 4
                ]);
            }
        }

        $data = [
            'title' => 'فروشگاه اینترنتی یدک صدرا',
            'order_code' => $order->order_code,
            'order' => $order,
            'address'=> json_decode($order->address, true),
            'user' => $user,
            'orderItems'=> $orderItems,
            'transactions'=> $transactions,
            'isPost'=> true
        ];

        $pdf = Pdf::loadView('invoice', compact('data'),[],[
            'format' => 'A5-L',
            'margin_left'              => 20,
            'margin_right'             => 20,
            'margin_top'               => 20,
            'margin_bottom'            => 20,
        ]);
        return $pdf->stream($order->order_code.'.pdf');
    }
}
