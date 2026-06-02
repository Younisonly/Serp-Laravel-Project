<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Product;
use App\Models\Support;
use Illuminate\Http\Request;

class EntityCheckController extends Controller
{
    public function check(Request $request, string $type, int $id)
    {
        $exists = match ($type) {
            'product' => Product::where('id', $id)->exists(),
            'support' => Support::where('id', $id)->exists(),
            'comment' => Comment::where('id', $id)->exists(),
            default => null,
        };

        if ($exists === null) {
            return response()->json([
                'success' => false,
                'message' => "Unknown entity type: $type",
            ], 400);
        }

        return response()->json([
            'success' => true,
            'exists' => $exists,
        ]);
    }
}
