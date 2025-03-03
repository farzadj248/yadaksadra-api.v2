<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id"=> $this->id,
            "personnel_code"=> $this->personnel_code,
            "avatar"=> $this->avatar,
            "first_name"=> $this->first_name,
            "last_name"=> $this->last_name,
            "mobile_number"=> $this->mobile_number,
            "role"=> $this->role,
            "status"=> $this->status,
            "created_at"=> $this->created_at->format('Y-m-d H:i:s'),
            "email"=> $this->email
        ];
    }
}
