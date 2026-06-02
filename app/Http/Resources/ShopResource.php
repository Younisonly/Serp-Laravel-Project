<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'logo' => $this->logo ? asset($this->logo) : null,
            'backlogo' => $this->backlogo ? asset($this->backlogo) : null,
            'iconname' => $this->iconname,
            'status' => $this->status,
            'is_open' => $this->is_open,
            'business_type' => $this->business_type,
            'address' => $this->address,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'open_at' => $this->open_at?->format('H:i'),
            'close_at' => $this->close_at?->format('H:i'),
            'days_open' => $this->days_open ? explode(',', $this->days_open) : [],
            'rating' => (float) $this->stars_rate,
            'raters_count' => $this->raters_number ?? 0,
            'followers_count' => $this->followers_number ?? 0,
            'views_count' => $this->views ?? 0,
            'points' => (float) $this->points,
            'verification_status' => $this->verification_status,
            'verification_note' => $this->when($request->user()?->id === $this->owner_id, $this->verification_note),
            'contact_email' => $this->contact_email,
            'price_range' => $this->price_range,
            'amenities' => $this->amenities ? (is_array($this->amenities) ? $this->amenities : explode(',', $this->amenities)) : [],
            'website' => $this->website,
            'instagram' => $this->instagram,
            'twitter' => $this->X,
            'whatsapp' => $this->whatsapp,
            'facebook' => $this->facebook,
            'phones' => $this->whenLoaded('phones', fn() => $this->phones->pluck('number')),
            'images' => $this->whenLoaded('images', fn() => $this->images->map(fn($img) => [
                'id' => $img->id,
                'url' => asset($img->url),
                'is_primary' => (bool) $img->is_primary,
                'status' => $img->status,
                'created_at' => $img->created_at->toISOString(),
            ])),
            'primary_image' => $this->whenLoaded('primaryImage', fn() => $this->primaryImage->first()?->url ? asset($this->primaryImage->first()->url) : null),
            'owner_id' => $this->owner_id,
            'joined_at' => $this->joined_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
