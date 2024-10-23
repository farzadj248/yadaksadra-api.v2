<?php

namespace App\Exports;

use App\Models\ProductCarTypes;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportProductCar implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {        
        return ProductCarTypes::select("id", "title","en_title","company_id","company_name", "order", "image_url", "created_at")->get();
    }

    public function headings(): array
    {
        return [
            "کد", 
            "خودرو",
            "عنوان لاتین",
            "شناسه شرکت خودروسازی",
            "شرکت خودروسازی",
            "ترتیب", 
            "آدرس تصویر", 
            "تاریخ و زمان ثبت"
        ];
    }
}
