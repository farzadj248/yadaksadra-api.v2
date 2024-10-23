<?php

namespace App\Imports;

use App\Models\ProductsCategories;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportProductCategory implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ProductsCategories([
            'title' => $row[0],
            'parent_id' => $row[1],
            'order' => $row[2],
            'image_url'=> $row[3]
        ]);
    }
}