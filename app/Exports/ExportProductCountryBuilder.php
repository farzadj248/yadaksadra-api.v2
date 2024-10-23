<?php

namespace App\Exports;

use App\Models\ProductCountryBuilders;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportProductCountryBuilder implements FromCollection,WithHeadings
{

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {        
        return ProductCountryBuilders::select("id", "title", "order", "image_url", "created_at")->get();
    }

    public function headings(): array
    {
        return [
            "کد", 
            "کشور", 
            "ترتیب", 
            "آدرس تصویر",
            "تاریخ و زمان ثبت"
        ];
    }
}
