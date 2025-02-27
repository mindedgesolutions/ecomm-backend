<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'brand_id' => $this->brand_id,
            'brand_name' => $this->brand->name,
            'category_id' => $this->category_id,
            'category_name' => $this->category->name,
            'parent_category_id' => $this->category?->parent?->id,
            'parent_category_name' => $this->category?->parent?->name,
            'name' => $this->name,
            'slug' => $this->slug,
            'product_code' => $this->product_code,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'images' => $this->images,
            'discount' => $this->discount,
        ];
    }
}
