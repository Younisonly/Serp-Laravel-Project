<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $types = [
            [
                'type' => 'default',
                'width' => 1200,
                'height' => 1200,
                'max_size_mb' => 2,
                'quality' => 85,
                'watermark_opacity' => 60,
            ],
            [
                'type' => 'user',
                'width' => 800,
                'height' => 800,
                'max_size_mb' => 1,
                'quality' => 80,
                'watermark' => 'watermarks/download.png',
                'watermark_opacity' => 60,
            ],
            [
                'type' => 'avatar',
                'width' => 400,
                'height' => 400,
                'max_size_mb' => 2,
                'quality' => 85,
                'watermark' => 'watermarks/download.png',
                'watermark_opacity' => 60,
            ],
            [
                'type' => 'shop_logo',
                'width' => 600,
                'height' => 600,
                'max_size_mb' => 3,
                'quality' => 85,
                'watermark' => 'watermarks/download.png',
                'watermark_opacity' => 60,
            ],
            [
                'type' => 'shop_cover',
                'width' => 1920,
                'height' => 600,
                'max_size_mb' => 5,
                'quality' => 85,
                'watermark' => 'watermarks/download.png',
                'watermark_opacity' => 60,
            ],
            [
                'type' => 'product',
                'width' => 800,
                'height' => 800,
                'max_size_mb' => 3,
                'quality' => 80,
                'watermark' => 'watermarks/download.png',
                'watermark_opacity' => 60,
            ],
            [
                'type' => 'banner',
                'width' => 1920,
                'height' => 600,
                'max_size_mb' => 5,
                'quality' => 80,
                'watermark' => 'watermarks/download.png',
                'watermark_opacity' => 40,
            ],
            [
                'type' => 'backlogo',
                'width' => 1920,
                'height' => 600,
                'max_size_mb' => 5,
                'quality' => 80,
                'watermark' => 'watermarks/download.png',
                'watermark_opacity' => 40,
            ],
        ];

        foreach ($types as $type) {
            DB::table('image_settings')->updateOrInsert(
                ['type' => $type['type']],
                $type
            );
        }
    }

    public function down(): void
    {
        DB::table('image_settings')->whereIn('type', ['default', 'user', 'avatar', 'shop_logo', 'shop_cover', 'product', 'banner', 'backlogo'])->delete();
    }
};
