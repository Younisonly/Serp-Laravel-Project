<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => asset($this->url),
            'is_primary' => (bool) $this->is_primary,
            'status' => $this->status,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
