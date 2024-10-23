<?php

namespace App\Imports;

use App\Models\Product;
use App\Helper\GenerateSlug;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportProduct implements ToModel
{
    protected $count=0;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if($this->count==0){
            $this->count++;
            return;
        }
        
        if(empty($row[0])) return;
        
        return new Product([
            "title" => $row[0], 
            "slug" => GenerateSlug::get($row[0]), 
            "main_unique_identifier" => $row[1]??'', 
            "market_unique_identifier" => $row[2]??'', 
            "custom_unique_identifier" => $row[3]??'',
            "technical_code" => $row[4]??'',
            "commercial_code" => $row[5]??'', 
            "short_body" => $row[6]??'',
            "main_inventory_2" => 0, 
            "main_inventory_3" => 0, 
            "market_inventory" => 0, 
            "market_inventory_2" => 0, 
            "market_inventory_3" => 0, 
            "custom_inventory" => 0, 
            "custom_inventory_2" => 0, 
            "custom_inventory_3" => 0, 
            "main_price" => 0, 
            "main_price_2" => 0, 
            "main_price_3" => 0, 
            "custom_price" => 0, 
            "custom_price_2" => 0, 
            "custom_price_3" => 0, 
            "market_price" => 0, 
            "market_price_2" => 0, 
            "market_price_3" => 0, 
            "main_off" => 0, 
            "main_off_2" => 0, 
            "main_off_3" => 0, 
            "market_off" => 0, 
            "market_off_2" => 0, 
            "market_off_3" => 0, 
            "custom_off" => 0, 
            "custom_off_2" => 0, 
            "custom_off_3" => 0, 
            "main_minimum_purchase" => 0, 
            "main_minimum_purchase_2" => 0, 
            "main_minimum_purchase_3" => 0, 
            "market_minimum_purchase" => 0, 
            "market_minimum_purchase_2" => 0, 
            "market_minimum_purchase_3" => 0, 
            "custom_minimum_purchase" => 0, 
            "custom_minimum_purchase_2" => 0, 
            "custom_minimum_purchase_3" => 0
        ]);
    }
}