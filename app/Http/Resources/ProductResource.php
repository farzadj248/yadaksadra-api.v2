<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'id' => $this->id,
            'commercial_code' => $this->commercial_code,
            'technical_code' => $this->technical_code,
            'title' => $this->title,
            'slug' => $this->slug,
            'status' => $this->status,
            'price' => $this->main_price,
            'brand_name' => $this->brand_name,
            'rating' => $this->rating,
            'views' => $this->views,
            'image' => $this->image->url[0] ?? null,
        ];
    }
}
