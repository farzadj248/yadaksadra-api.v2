<?php

namespace App\Exports;

use App\Models\Orders;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportOrders implements FromCollection,WithHeadings
{
    public function __construct(int $status)
    {
        $this->status = $status;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        if($this->status==0){
            return Orders::leftJoin('users', function ($query) {
                $query->on('users.id', '=', 'orders.user_id');
            })
            ->select('orders.order_code','orders.total','users.first_name','users.last_name','orders.gateway_pay','orders.created_at')
            ->get();
        }

        return Orders::leftJoin('users', function ($query) {
            $query->on('users.id', '=', 'orders.user_id');
        })
        ->select('orders.order_code','orders.total','users.first_name','users.last_name','orders.gateway_pay','orders.created_at')
        ->where("orders.status",$this->status)
        ->get();
    }

    public function headings(): array
    {
        return [
            "شماره سفارش",
            "مبلغ کل صورتحساب",
            "نام پرداخت کننده",
            "نام خانوادگی پرداخت کننده",
            "درگاه پرداخت"
            ,"تایخ ثبت سفارش"];
    }
}
