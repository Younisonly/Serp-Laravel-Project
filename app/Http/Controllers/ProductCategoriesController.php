<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductCategoriesController extends Controller
{
    public function index()
    {
        try {
            $categories = Cache::remember('product_categories_active', 3600, function () {
                return ProductCategory::active()->select('id', 'name', 'name_en', 'name_ar', 'icon', 'sort_order', 'is_active')->get();
            });

            return response()->json([
                'status' => 'success',
                'data' => $categories,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('[ProductCategoriesController] index failed: ' . $e->getMessage(), [
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
