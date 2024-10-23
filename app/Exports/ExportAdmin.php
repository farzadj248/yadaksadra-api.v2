<?php

namespace App\Exports;

use App\Models\Admin;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportAdmin implements FromCollection,WithHeadings
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
        if($this->status==2){
            return Admin::select('personnel_code', 'first_name', 'last_name', 'user_name', 'mobile_number', 'phone_number',
        'national_code', 'birth_date', 'email', 'address', 'province', 'city')
            ->get();
        }

        return Admin::select('personnel_code', 'first_name', 'last_name', 'user_name', 'mobile_number', 'phone_number',
        'national_code', 'birth_date', 'email', 'address', 'province', 'city')
        ->where("status",$this->status)
        ->get();
    }

    public function headings(): array
    {
        return [
            'کد پرسنلی',
            'نام',
            'نام خانوادگی',
            'نام کاربری',
            'شماره موبایل',
            'شماره تلفن',
            'کد ملی',
            'تاریخ تولد',
            'ایمیل',
            'آدرس محل سکونت',
            'استان محل سکونت',
            'شهرستان محل سکونت'
        ];
    }
}
