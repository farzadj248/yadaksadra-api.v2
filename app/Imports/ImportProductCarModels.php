<?php

namespace App\Imports;

use App\Models\ProductCarModels;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportProductCarModels implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ProductCarModels([
            'title' => $row[0],
            'car_id' => $row[1],
            'car_name' => $row[2]
        ]);
    }
}