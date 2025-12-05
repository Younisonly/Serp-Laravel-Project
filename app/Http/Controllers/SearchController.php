<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
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
            $limit = $request->input('limit', 10);
            $lat = $request->input('lat');
            $lng = $request->input('lng');

            $shopsQuery = Shop::select(
                'id',
                'name',
                'logo',
                'lat',
                'lng',
                'iconname',
                'status',
                'stars_rate'
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

            if ($query != 'all') {
                $shopsQuery->where('name', 'like', "%{$query}%");
            }

            $shops = $shopsQuery->paginate($limit);

            return response()->json($shops);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
                'error_message' => $e->getMessage(),
            ], 500);
        }
    }
}
