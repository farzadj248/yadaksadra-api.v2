<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportProductPrice implements ToModel
{
    protected $count=0;
    
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // if($this->count==0){
        //     $this->count++;
        //     return;
        // }
        
        if(empty($row[0])) return;
        
        $product=Product::where("id",$row[0])->first();

        if($product){
            $product->update([
                // 'title'=> !empty($row[1])?$row[1]:$product->title,
                'main_price'=> !empty($row[2])?$row[2]:$product->main_price,
                'main_inventory'=> !empty($row[3])?$row[3]:$product->main_inventory,
                'main_off'=> !empty($row[4])?$row[4]:$product->main_off,
                'main_minimum_purchase'=> !empty($row[5])?$row[5]:$product->main_minimum_purchase,
                'custom_price'=> !empty($row[6])?$row[6]:$product->custom_price,
                'custom_off'=> !empty($row[7])?$row[7]:$product->custom_off,
                'custom_inventory'=> !empty($row[8])?$row[8]:$product->custom_inventory,
                'custom_minimum_purchase'=> !empty($row[9])?$row[9]:$product->custom_minimum_purchase,
                'market_price'=> !empty($row[10])?$row[10]:$product->market_price,
                'market_off'=> !empty($row[11])?$row[11]:$product->market_off,
                'market_inventory'=> !empty($row[12])?$row[12]:$product->market_inventory,
                'market_minimum_purchase'=> !empty($row[13])?$row[13]:$product->market_minimum_purchase,
                'main_price_2'=> !empty($row[14])?$row[14]:$product->main_price_2,
                'main_off_2'=> !empty($row[15])?$row[15]:$product->main_off_2,
                'main_inventory_2'=> !empty($row[16])?$row[16]:$product->main_inventory_2,
                'main_minimum_purchase_2'=> !empty($row[17])?$row[17]:$product->main_minimum_purchase_2,
                'market_price_2'=> !empty($row[18])?$row[18]:$product->market_price_2,
                'market_off_2'=> !empty($row[19])?$row[19]:$product->market_off_2,
                'market_inventory_2'=> !empty($row[20])?$row[20]:$product->market_inventory_2,
                'market_minimum_purchase_2'=> !empty($row[21])?$row[21]:$product->market_minimum_purchase_2,
                'custom_price_2'=> !empty($row[22])?$row[22]:$product->custom_price_2,
                'custom_off_2'=> !empty($row[23])?$row[23]:$product->custom_off_2,
                'custom_inventory_2'=> !empty($row[24])?$row[24]:$product->custom_inventory_2,
                'custom_minimum_purchase'=> !empty($row[25])?$row[25]:$product->custom_minimum_purchase,
                'main_price_3'=> !empty($row[26])?$row[26]:$product->main_price_3,
                'main_off_3'=> !empty($row[27])?$row[27]:$product->main_off_3,
                'main_inventory_3'=> !empty($row[28])?$row[28]:$product->main_inventory_3,
                'main_minimum_purchase_3'=> !empty($row[29])?$row[29]:$product->main_minimum_purchase_3,
                'market_price_3'=> !empty($row[30])?$row[30]:$product->market_price_3,
                'market_off_3'=> !empty($row[31])?$row[31]:$product->market_off_3,
                'market_inventory_3'=> !empty($row[32])?$row[32]:$product->market_inventory_3,
                'market_minimum_purchase_3'=> !empty($row[33])?$row[33]:$product->market_minimum_purchase_3,
                'custom_price_3'=> !empty($row[34])?$row[34]:$product->custom_price_3,
                'custom_off_3'=> !empty($row[35])?$row[35]:$product->custom_off_3,
                'custom_inventory_3'=> !empty($row[36])?$row[36]:$product->custom_inventory_3,
                'custom_minimum_purchase_3'=> !empty($row[37])?$row[37]:$product->custom_minimum_purchase_3,
            ]);
         }
            
        return $product;
    }
}