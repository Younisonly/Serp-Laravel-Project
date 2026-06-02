<?php

namespace App\Http\Controllers;

use App\Models\ImageSetting;
use Illuminate\Http\Request;

class ImageSettingsController extends Controller
{
    public function show(string $type)
    {
        $settings = ImageSetting::where('type', $type)->first()
            ?? ImageSetting::where('type', 'default')->first();

        if (!$settings) {
            return response()->json([
                'success' => false,
                'message' => 'No image settings found',
            ], 404);
        }

        $watermarkUrl = null;
        if (!empty($settings->watermark)) {
            $relativePath = ltrim($settings->watermark, '/');
            $storagePath = storage_path('app/public/' . $relativePath);
            if (file_exists($storagePath)) {
                $watermarkUrl = url('/storage/' . $relativePath);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'type' => $settings->type,
                'width' => (int) $settings->width,
                'height' => (int) $settings->height,
                'max_size_mb' => (float) $settings->max_size_mb,
                'quality' => (int) ($settings->quality ?? 85),
                'watermark_url' => $watermarkUrl,
                'watermark_opacity' => (int) ($settings->watermark_opacity ?? 60),
            ],
        ]);
    }
}
