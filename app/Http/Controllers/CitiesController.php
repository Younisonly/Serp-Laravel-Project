<?php

namespace App\Http\Controllers;

use App\Models\cities;

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
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong while fetching cities',
                'error_message' => $e->getMessage(),
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
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong while fetching cities for the country',
                'error_message' => $e->getMessage(),
            ], 500);
        }
    }
}
