<?php

namespace App\Exports;

use App\Models\ProductCarModels;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportProductCarModel implements FromCollection,WithHeadings
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
        return ProductCarModels::select("id", "title","car_id", "car_name", "created_at")->where("car_id",$this->id)->get();
    }

    public function headings(): array
    {
        return [
            "کد", 
            "مدل خودرو", 
            "کد شرکت خودروسازی",
            "شرکت خودرو سازی", 
            "تاریخ و زمان ثبت"
        ];
    }
}
