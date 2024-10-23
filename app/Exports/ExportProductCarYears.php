<?php

namespace App\Exports;

use App\Models\ProductCarYears;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportProductCarYears implements FromCollection,WithHeadings
{

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {        
        return ProductCarYears::select("id", "title", "model_id", "model_name", "created_at")->where("model_id",$this->id)->get();
    }

    public function headings(): array
    {
        return [
            "کد", 
            "سال ساخت", 
            "کد مدل خودرو",
            "مدل خودرو", 
            "تاریخ و زمان ثبت",
        ];
    }
}
