<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /*
    user role
    1.Normal
    2.Marketer
    3.Organization
    4.Saler
    
    request change role
    0.no any request
    1.request for normal role
    2.request for marketer role
    3.request for organization role
    4.request for saler role
    
    request change role status
    0.no any status
    1.pending
    2.confirmed
    3.rejected

    documents status
    0.Unknown
    1.pending
    2.confirmed
    3.rejected

    credit purchase type
    0- deactive
    1 - Required documents up to the credit limit 50،000،000
    2 - Required documents up to the credit limit 150،000،000
    3 - Required documents up to the credit limit 500،000،000
    
    request_credit_again 
    0.deactive
    1.active
    */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'personnel_code', 'uuid', 'first_name', 'full_name','last_name', 'user_name',
        'avatar', 'father_name', 'mobile_number', 'phone_number',
        'national_code', 'birth_date', 'email', 'agency', 'wallet_balance',
        'income', 'shaba_bank', 'biography', 'status',
        'role','password', 'company_name',
        'documents', 'documents_status','credit_purchase_type','request_credit_again',
        'affiliate_id','invited_affiliate_confirmed','invited_affiliate_pending',
        'clicks',
        'field_of_activity',
        'province_of_activity_id','province_of_activity',
        'city_of_activity','city_of_activity_id',
        'job_position','credit_purchase_inventory',
        'request_change_role'
    ];

    /**
     * datetime cast
     *
     * @var array
     */
    protected $casts = [
        'created_at' => "datetime:Y-m-d H:i:s",
        'updated_at' => "datetime:Y-m-d H:i:s",
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
}