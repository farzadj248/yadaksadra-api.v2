<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            "id" => $this->id,
            "order_code" => $this->order_code,
            "total" => number_format($this->total),
            "full_name" => $this->first_name .' '.$this->last_name,
            "status" => $this->status,
            "date" => $this->created_at->format('Y-m-d H:i:s')
        ];
    }
}
