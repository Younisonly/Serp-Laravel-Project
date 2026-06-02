<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class AmenityController extends Controller
{
    public function index(Request $request)
    {
        try {
            $category = $request->query('category');
            $cacheKey = $category ? "amenities_active_{$category}" : 'amenities_active';

            $amenities = Cache::remember($cacheKey, 3600, function () use ($category) {
                $query = Amenity::active();
                if ($category) {
                    $query->byCategory($category);
                }
                return $query->get();
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Amenities retrieved successfully',
                'data'    => $amenities,
            ], 200);

        } catch (Throwable $e) {
            Log::error('[AmenityController] index failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $amenity = Amenity::find($id);

            if (!$amenity) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Amenity not found',
                ], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Amenity retrieved successfully',
                'data'    => $amenity,
            ], 200);

        } catch (Throwable $e) {
            Log::error('[AmenityController] show failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function clearCache()
    {
        $cacheKeys = ['amenities_active'];
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
        Cache::tags(['amenities'])->flush();
        return response()->json(['status' => 'success', 'message' => 'Cache cleared']);
    }
}
