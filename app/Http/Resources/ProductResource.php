<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'discount' => (float) $this->discount,
            'effective_price' => $this->discount > 0
                ? round($this->price * (1 - $this->discount / 100), 2)
                : (float) $this->price,
            'discount_start' => $this->discount_start?->format('Y-m-d'),
            'discount_end' => $this->discount_end?->format('Y-m-d'),
            'is_discount_active' => $this->isDiscountActive(),
            'brand' => $this->brand,
            'model_number' => $this->model_number,
            'color' => $this->color,
            'size' => $this->size,
            'material' => $this->material,
            'weight' => $this->weight ? (float) $this->weight : null,
            'barcode' => $this->barcode,
            'warranty' => (bool) $this->warranty,
            'warranty_start' => $this->warranty_start?->format('Y-m-d'),
            'warranty_end' => $this->warranty_end?->format('Y-m-d'),
            'warranty_description' => $this->warranty_description,
            'highlights' => $this->highlights ?? [],
            'specifications' => $this->specifications ?? [],
            'tags' => $this->tags ?? [],
            'shipping_info' => $this->shipping_info,
            'return_policy' => $this->return_policy,
            'delivery_info' => $this->delivery_info,
            'notes' => $this->notes,
            'rating' => (float) $this->stars_rate,
            'raters_count' => $this->raters_number ?? 0,
            'views_count' => $this->views ?? 0,
            'shop_id' => $this->shop_id,
            'shop_name' => $this->whenLoaded('shop', fn() => $this->shop->name),
            'shop_logo' => $this->whenLoaded('shop', fn() => $this->shop->logo ? asset($this->shop->logo) : null),
            'category_id' => $this->type_id,
            'category_name' => $this->whenLoaded('category', fn() => $this->category->name),
            'primary_image' => $this->whenLoaded('primaryImage', fn() => $this->primaryImage?->url ? asset('storage/' . $this->primaryImage->url) : null),
            'images' => $this->whenLoaded('images', fn() => $this->images->map(fn($img) => [
                'id' => $img->id,
                'url' => asset('storage/' . $img->url),
                'is_primary' => (bool) $img->is_primary,
            ])),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'favorites_count' => $this->favorites_count ?? 0,
            'likes_count' => $this->likes_count ?? 0,
        ];
    }

    protected function isDiscountActive(): bool
    {
        if ($this->discount <= 0) return false;
        $now = now();
        if ($this->discount_start && $now->lt($this->discount_start)) return false;
        if ($this->discount_end && $now->gt($this->discount_end)) return false;
        return true;
    }
}
