<?php

namespace App\Imports;

use App\Models\ProductsBrands;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportProductBrands implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ProductsBrands([
            'title' => $row[0],
            'parent_id' => $row[1],
            'order' => $row[2],
            'image_url'=> $row[3]
        ]);
    }
}