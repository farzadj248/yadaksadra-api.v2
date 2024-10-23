<?php

namespace App\Exports;

use App\Models\ProductCarCompany;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportProductCarCompany implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {        
        return ProductCarCompany::select("id","title", "en_title", "order", "image_url", "created_at")->get();
    }

    public function headings(): array
    {
        return [
            "کد", 
            "شرکت خودروسازی", 
            "عنوان لاتین",
            "ترتیب", 
            "آدرس تصویر", 
            "تاریخ و زمان ثبت"
        ];
    }
}
