<?php

namespace App\Http\Controllers;

use App\Models\cities;
use Illuminate\Support\Facades\Log;

class CitiesController extends Controller
{
    public function index()
    {
        try {
            $cities = cities::select('id', 'name', 'country_id')->get();

            return response()->json([
                'status' => 'success',
                'data' => $cities
            ]);
        } catch (\Throwable $e) {
            Log::error('[CitiesController] index failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function byCountry($countryId)
    {
        try {
            $cities = cities::where('country_id', $countryId)
                ->select('id', 'name', 'country_id')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $cities
            ]);
        } catch (\Throwable $e) {
            Log::error('[CitiesController] byCountry failed: ' . $e->getMessage(), [
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
