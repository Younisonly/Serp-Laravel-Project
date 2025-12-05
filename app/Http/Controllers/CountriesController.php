<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;

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
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong while fetching countries',
                'error_message' => $e->getMessage(),
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
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong while fetching countries with cities',
                'error_message' => $e->getMessage(),
            ], 500);
        }
    }
}
