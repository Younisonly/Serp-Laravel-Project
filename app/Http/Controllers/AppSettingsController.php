<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;

class AppSettingsController extends Controller
{
    public function index()
    {
        $settings = AppSetting::all()->groupBy('group')->map(function ($items) {
            return $items->pluck('value', 'key');
        });

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }
}
