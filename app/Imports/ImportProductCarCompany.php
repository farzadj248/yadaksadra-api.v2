<?php

namespace App\Imports;

use App\Models\ProductCarCompany;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportProductCarCompany implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ProductCarCompany([
            'title' => $row[0],
            'en_title' => $row[1],
            'order' => $row[2],
            'image_url'=> $row[3],
        ]);
    }
}