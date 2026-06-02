<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'status' => $this->status,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'commenter' => $this->whenLoaded('commenter', fn() => [
                'id' => $this->commenter->id,
                'name' => $this->commenter->name,
                'image' => $this->commenter->image,
            ]),
            'reply' => $this->reply_content ? [
                'content' => $this->reply_content,
                'replyer_type' => $this->replyer_type,
                'created_at' => $this->replied_at?->toISOString(),
            ] : null,
            'reactions' => $this->whenLoaded('reactions', fn() => $this->reactions->map(fn($r) => [
                'id' => $r->id,
                'liker_id' => $r->liker_id,
                'value' => $r->value,
            ])),
            'likes_count' => $this->reactions?->where('value', 'like')->count() ?? 0,
            'dislikes_count' => $this->reactions?->where('value', 'dislike')->count() ?? 0,
        ];
    }
}
