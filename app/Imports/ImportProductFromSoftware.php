<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportProductFromSoftware implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if(empty($row[0])) return;
        
        $id=$row[0];
        $inventory=$row[9]?$row[9]>=0?$row[9]:0:0;
        
        //گرید اصلی
        $product1=Product::where("main_unique_identifier",$id)->first();
        if($product1){
            $marketer=$row[6]?$row[6]:$product1->main_price_2;
            $saler=$row[7]?$row[7]:$product1->main_price_3;
            $normal=$row[8]?$row[8]:$product1->main_price;
        
            $product1->update([
                'main_price'=> $normal,
                'main_price_2'=> $marketer,
                'main_price_3'=> $saler,
                'main_inventory'=> $inventory,
                'main_inventory_2'=> $inventory,
                'main_inventory_3'=> $inventory
            ]);
            
            return $product1;
        }
        
        //بازاری،کپی
        $product2=Product::where("market_unique_identifier",$id)->first();
        if($product2){
            $marketer=$row[6]?$row[6]:$product2->main_price_2;
            $saler=$row[7]?$row[7]:$product2->main_price_3;
            $normal=$row[8]?$row[8]:$product2->main_price;
        
            $product2->update([
                'market_price'=> $normal,
                'market_price_2'=> $marketer,
                'market_price_3'=> $saler,
                'market_inventory'=> $inventory,
                'market_inventory_2'=> $inventory,
                'market_inventory_3'=> $inventory
            ]);
            
            return $product2;
        }
        
        //سفارشی،های کپی
        $product3=Product::where("custom_unique_identifier",$id)->first();
        if($product3){
            $marketer=$row[6]?$row[6]:$product3->main_price_2;
            $saler=$row[7]?$row[7]:$product3->main_price_3;
            $normal=$row[8]?$row[8]:$product3->main_price;
        
            $product3->update([
                'custom_price'=> $normal,
                'custom_price_2'=> $marketer,
                'custom_price_3'=> $saler,
                'custom_inventory'=> $inventory,
                'custom_inventory_2'=> $inventory,
                'custom_inventory_3'=> $inventory
            ]);
            
            return $product3;
        }
    }
    
}