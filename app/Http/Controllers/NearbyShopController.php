<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NearbyShopController extends Controller
{
    public function nearbyShops(Request $request)
    {
        try {
            $lat = $request->input('lat');
            $lng = $request->input('lng');
            $radius = $request->input('radius', 100);

            // Validate required inputs
            if (!$lat || !$lng) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Latitude and longitude are required.'
                ], 400);
            }

            // Optional: cast inputs to float to ensure math safety
            $lat = (float) $lat;
            $lng = (float) $lng;
            $radius = (float) $radius;

            // Query nearby shops
            $shops = DB::table('shops') // ✅ Correct table name
                ->whereNotNull('lat')
                ->whereNotNull('lng')
                ->select('*', DB::raw("(6371 * ACOS(
                    COS(RADIANS($lat)) * COS(RADIANS(lat)) * COS(RADIANS(lng) - RADIANS($lng)) + 
                    SIN(RADIANS($lat)) * SIN(RADIANS(lat))
                )) AS distance"))
                ->having('distance', '<', $radius)
                ->orderBy('distance')
                ->limit(25)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $shops
            ]);
        } catch (\Throwable $e) {
            // Log the detailed error (optional)
            Log::error('NearbyShops Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return clear error message to API consumer
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong while fetching nearby shops.',
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                // 'trace' => $e->getTraceAsString(), // Uncomment only in development
            ], 500);
        }
    }
}
