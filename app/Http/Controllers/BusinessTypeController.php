<?php

namespace App\Http\Controllers;

use App\Models\BusinessType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class BusinessTypeController extends Controller
{
    public function index()
    {
        try {
            $types = Cache::remember('business_types_active', 3600, function () {
                return BusinessType::active()->get();
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Business types retrieved successfully',
                'data'    => $types,
            ], 200);

        } catch (Throwable $e) {
            Log::error('[BusinessTypeController] index failed: ' . $e->getMessage(), [
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
            $type = BusinessType::find($id);

            if (!$type) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Business type not found',
                ], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Business type retrieved successfully',
                'data'    => $type,
            ], 200);

        } catch (Throwable $e) {
            Log::error('[BusinessTypeController] show failed: ' . $e->getMessage(), [
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
        Cache::forget('business_types_active');
        return response()->json(['status' => 'success', 'message' => 'Cache cleared']);
    }
}
