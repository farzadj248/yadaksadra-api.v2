<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportUser implements FromCollection,WithHeadings
{

    public function __construct(int $type)
    {
        $this->type = $type;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {        
        if($this->type==0){
            return User::select('personnel_code','first_name','last_name','user_name','father_name','mobile_number','national_code','birth_date','email','income','shaba_bank')->get();
        }
        
        switch($this->type){
            case 1:
                return User::select('personnel_code','first_name','last_name','user_name','father_name','mobile_number','national_code','birth_date','email','income','shaba_bank')
                ->where("role","Normal")
                ->get();
                break;

            case 2:
                return User::select('personnel_code','first_name','last_name','user_name','father_name','mobile_number','national_code','birth_date','email','income','shaba_bank')
                ->where("role","Marketer")
                ->get();
                break;

            case 3:
                return User::select('personnel_code','first_name','last_name','user_name','father_name','mobile_number','national_code','birth_date','email','income','shaba_bank')
                ->where("role","Organization")
                ->get();
                break;

            case 4:
                return User::select('personnel_code','first_name','last_name','user_name','father_name','mobile_number','national_code','birth_date','email','income','shaba_bank')
                ->where("role","Saler")
                ->get();
                break;
        }
        
    }

    public function headings(): array
    {
        return [
            "شماره پرسنلی",
            "نام",
            "نام خانوادگی",
            "نام کاربری",
            "نام پدر",
            "شماره موبایل",
            "کد ملی",
            "تاریخ تولد",
            "ایمیل",
            "درآمد(تومان)",
            "شماره شبا"
        ];
    }
}
