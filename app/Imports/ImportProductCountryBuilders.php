<?php

namespace App\Imports;

use App\Models\ProductCountryBuilders;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportProductCountryBuilders implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ProductCountryBuilders([
            'title' => $row[0],
            'order' => $row[1],
            'image_url'=> $row[2]
        ]);
    }
}