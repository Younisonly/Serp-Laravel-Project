<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use Illuminate\Support\Facades\Log;

class CountriesController extends Controller
{
    public function index()
    {
        try {
            $countries = Country::select('id', 'name')->get();

            return response()->json([
                'status' => 'success',
                'data' => $countries
            ]);
        } catch (\Throwable $e) {
            Log::error('[CountriesController] index failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function withCities()
    {
        try {
            $countries = Country::with('cities:id,name,country_id')
                ->select('id', 'name')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $countries
            ]);
        } catch (\Throwable $e) {
            Log::error('[CountriesController] withCities failed: ' . $e->getMessage(), [
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
