<?php

namespace App\Imports;

use App\Models\ProductCarTypes;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportProductCars implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ProductCarTypes([
            'title' => $row[0],
            'en_title' => $row[1],
            'company_id' => $row[2], 
            'company_name' => $row[3],
            'order' => $row[4],
            'image_url'=> $row[5]
        ]);
    }
}