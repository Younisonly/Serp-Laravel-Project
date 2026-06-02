<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shop;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    private const CACHE_TTL = 60;

    public function search(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string|max:255',
                'page' => 'nullable|integer|min:1',
                'limit' => 'nullable|integer|min:1|max:100',
                'lat' => 'nullable|numeric',
                'lng' => 'nullable|numeric',
            ]);

            $query = $request->input('query');
            $limit = (int) $request->input('limit', 20);
            $page = max(1, (int) $request->input('page', 1));
            $lat = $request->input('lat');
            $lng = $request->input('lng');

            $cacheKey = "search:{$query}:{$page}:{$limit}";

            $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $limit, $page, $lat, $lng) {
                $shopsQuery = Shop::select(
                    'id', 'name', 'logo', 'description', 'lat', 'lng',
                    'iconname', 'status', 'stars_rate',
                    'address', 'open_at', 'close_at',
                    'followers_number', 'views'
                );

                if ($lat && $lng) {
                    $shopsQuery->addSelect(DB::raw("
                        (6371 * acos(
                            cos(radians($lat)) * cos(radians(lat)) *
                            cos(radians(lng) - radians($lng)) +
                            sin(radians($lat)) * sin(radians(lat))
                        )) AS distance
                    "));
                }

                if ($query !== 'all') {
                    $shopsQuery->where('name', 'like', "%{$query}%");
                }

                $total = $shopsQuery->count();
                $shops = $shopsQuery->skip(($page - 1) * $limit)
                    ->take($limit)
                    ->get();

                return [
                    'data' => $shops,
                    'meta' => [
                        'current_page' => $page,
                        'per_page' => $limit,
                        'total' => $total,
                        'has_more' => ($page * $limit) < $total,
                    ]
                ];
            });

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
                'error_message' => $e->getMessage(),
            ], 500);
        }
    }
}
