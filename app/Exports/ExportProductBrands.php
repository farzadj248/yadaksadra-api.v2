<?php

namespace App\Exports;

use App\Models\ProductsBrands;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportProductBrands implements FromCollection,WithHeadings
{

    public function __construct(int $parent)
    {
        $this->parent = $parent;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {        
        return ProductsBrands::select("id", "title", "parent_id", "order", "image_url", "created_at")->where("parent_id",$this->parent)->get();
    }

    public function headings(): array
    {
        return [
            "کد", 
            "برند", 
            "کد برند مادر",
            "ترتیب", 
            "آدرس تصویر", 
            "تاریخ و زمان ثبت"
        ];
    }
}
