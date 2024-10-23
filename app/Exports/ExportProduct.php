<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportProduct implements FromCollection,WithHeadings
{
    public function __construct(int $status)
    {
        $this->status = $status;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $products=Product::select('id', 'market_unique_identifier', 'main_unique_identifier','custom_unique_identifier', 
            'commercial_code', 'technical_code', 'title','short_body', 'long_body',
            'main_price','main_inventory','main_off','main_minimum_purchase',
            'main_price_2','main_inventory_2','main_off_2','main_minimum_purchase_2',
            'main_price_3','main_inventory_3','main_off_3','main_minimum_purchase_3',
            'custom_price','custom_off','custom_inventory','custom_minimum_purchase',
            'custom_price_2','custom_off_2','custom_inventory_2','custom_minimum_purchase_2',
            'custom_price_3','custom_off_3','custom_inventory_3','custom_minimum_purchase_3',
            'market_price','market_off','market_inventory','market_minimum_purchase',
            'market_price_2','market_off_2','market_inventory_2','market_minimum_purchase_2',
            'market_price_3','market_off_3','market_inventory_3','market_minimum_purchase_3',
            'number_sales','views','rating', 'category_name','brand_name', 'country_name', 'video_url', 'tags');
            
            
        if($this->status==2){
            return $products->get();
        }

        if($this->status==3){
            return $products->where("inventory","!=",0)->get();
        }

        if($this->status==4){
            return $products->where("inventory",0)->get();
        }

        return $products->where("status",$this->status)->get();
    }

    public function headings(): array
    {
        return [
            'شناسه محصول',
            'شاسه یکتا بازاری',
            'شناسه یکتا اصلی',
            'شناسه یکتا سفارشی',
            'کد تجاری',
            'کد فنی', 
            'نام کالا',
            'توضیحات کوتاه', 
            'توضیحات',
            'قیمت گرید اصلی-عادی و سازمان',
            'موجودی انبار',
            'درصد تخفیف',
            'حداقل تعداد سفارش',
            'قیمت گرید اصلی-پخش و بازاریاب',
            'موجودی انبار',
            'درصد تخفیف',
            'حداقل تعدا سفارش',
            'قیمت گرید اصلی-فروشندگان لوازم یدکی',
            'موجودی انبار',
            'درصد تخفیف',
            'حداقل تعداد سفارش',
            'قیمت گرید سفارشی-عادی و سازمان',
            'درصد تخفیف',
            'موجودی انبار',
            'حداقل تعداد سفارش',
            'قیمت گرید سفارشی-پخش و بازاریاب',
            'درصد تخفیف',
            'موجودی انبار',
            'حداقل تعداد سفارش',
            'قیمت گرید سفارشی-فروشندگان لوازم یدکی',
            'درصد تخفیف',
            'موجودی انبار',
            'حداقل تعداد سفارش',
            'قیمت گرید بازاری-عادی و سازمان',
            'درصد تخفیف',
            'موجودی انبار',
            'حداقل تعداد سفارش',
            'قیمت گرید بازاری-پخش و بازاریاب',
            'درصد تخفیف',
            'موجودی انبار',
            'حداقل تعداد سفارش',
            'قیمت گرید بازاری-فروشندگان لوازم یدکی',
            'درصد تخفیف',
            'موجودی انبار',
            'حداقل تعداد سفارش',
            'تعداد فروش',
            'تعداد بازدید', 
            'امتیاز', 
            'دسته بندی',
            'برند', 
            'کشور سازنده', 
            'آدرس ویدیو', 
            'برچسب ها'
        ];
    }
}
