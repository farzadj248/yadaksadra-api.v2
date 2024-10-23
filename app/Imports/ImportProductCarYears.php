<?php

namespace App\Imports;

use App\Models\ProductCarYears;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportProductCarYears implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ProductCarYears([
            'title' => $row[0],
            'model_id' => $row[1],
            'model_name' => $row[2]
        ]);
    }
}