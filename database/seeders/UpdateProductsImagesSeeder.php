<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateProductsImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $records = DB::table('products_images')->get();

   

        foreach ($records as $record) {
            $data = json_decode($record->url, true);
         

            if (is_array($data)) {
                foreach ($data as &$item) {
                    if (!isset($item['order'])) {
                        $item['order'] = 0;
                    }
                }
        

                DB::table('products_images')
                    ->where('id', $record->id)
                    ->update(['url' => $data]);
            }
        }
        echo "ProductsImages table updated successfully as arrays!\n";
    }
}
