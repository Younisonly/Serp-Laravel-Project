<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NearbyShopController extends Controller
{
    private const CACHE_TTL = 60;
    private const DEFAULT_LIMIT = 50;
    private const MAX_LIMIT = 200;

    public function nearbyShops(Request $request)
    {
        try {
            $lat = (float) $request->input('lat');
            $lng = (float) $request->input('lng');
            $radius = (float) $request->input('radius', 50);
            $limit = min((int) $request->input('limit', self::DEFAULT_LIMIT), self::MAX_LIMIT);
            $page = max(1, (int) $request->input('page', 1));
            $businessType = $request->input('category', '');
            $openNow = filter_var($request->input('open_now', 'false'), FILTER_VALIDATE_BOOLEAN);

            if (!$lat || !$lng) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Latitude and longitude are required.'
                ], 400);
            }

            $gridLat = round($lat, 2);
            $gridLng = round($lng, 2);
            $cacheKey = "nearby:{$gridLat}:{$gridLng}:{$radius}:{$limit}:{$page}:{$businessType}:{$openNow}";

            $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($lat, $lng, $radius, $limit, $page, $businessType, $openNow) {
                $latDelta = $radius / 111.0;
                $lngDelta = $radius / (111.0 * cos(deg2rad($lat)));

                $minLat = $lat - $latDelta;
                $maxLat = $lat + $latDelta;
                $minLng = $lng - $lngDelta;
                $maxLng = $lng + $lngDelta;

                $haversine = "(6371 * ACOS(
                    COS(RADIANS(lat)) * COS(RADIANS($lat)) *
                    COS(RADIANS(lng) - RADIANS($lng)) +
                    SIN(RADIANS(lat)) * SIN(RADIANS($lat))
                ))";

                $query = DB::table('shops')
                    ->select('*', DB::raw("{$haversine} AS distance"))
                    ->whereNotNull('lat')
                    ->whereNotNull('lng')
                    ->whereBetween('lat', [$minLat, $maxLat])
                    ->whereBetween('lng', [$minLng, $maxLng]);

                if ($businessType !== '') {
                    $query->where('business_type', $businessType);
                }

                if ($openNow) {
                    $query->where('status', 'open');
                }

                $total = $query->count();

                $shops = $query
                    ->having('distance', '<', $radius)
                    ->orderBy('distance')
                    ->skip(($page - 1) * $limit)
                    ->take($limit)
                    ->get();

                return ['data' => $shops, 'total' => $total];
            });

            return response()->json([
                'status' => 'success',
                'data' => $result['data'],
                'total' => $result['total'] ?? 0,
                'current_page' => $page,
                'last_page' => (int) ceil(($result['total'] ?? 0) / $limit),
            ]);
        } catch (\Throwable $e) {
            Log::error('[NearbyShopController] nearbyShops failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }
}
