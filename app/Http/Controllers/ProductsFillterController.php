<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductsFillterController extends Controller
{
    public function Productfilter(Request $request)
    {
        try {
            $request->validate([
                'query' => 'nullable|string|max:255',
                'product_type' => 'nullable|string',
                'type' => 'nullable|string',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0',
                'discount_only' => 'nullable|string|in:true,false,1,0',
                'discount' => 'nullable|string|in:true,false,1,0',
                'order_by' => 'nullable|string|in:most_rated,latest,rating,price_low,price_high',
                'page' => 'nullable|integer|min:1',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);

            $query = $request->input('query', '');
            $product_type = $request->input('product_type', $request->input('type', ''));
            $hasMinPrice = $request->has('min_price');
            $hasMaxPrice = $request->has('max_price');
            $min_price = $request->input('min_price', 0);
            $max_price = $request->input('max_price', 500000);
            $order_by = $request->input('order_by', '');
            $limit = $request->input('limit', 10);
            $discountOnly = $request->input('discount_only', $request->input('discount', 'false'));
            $discount_only = filter_var($discountOnly, FILTER_VALIDATE_BOOLEAN);

            $page = $request->input('page', 1);
            $cacheKey = 'pf_' . md5(json_encode($request->except('page'))) . "_p{$page}_l{$limit}";

            $response = Cache::remember($cacheKey, 60, function () use ($query, $product_type, $hasMinPrice, $hasMaxPrice, $min_price, $max_price, $order_by, $limit, $page, $discount_only) {
                $productsQuery = Product::with(['primaryImage', 'category', 'shop:id,name,logo']);

                if ($query !== '' && $query !== 'all') {
                    $productsQuery->where('name', 'like', "%{$query}%");
                }

                if ($product_type !== '') {
                    $productsQuery->whereHas('category', function ($q) use ($product_type) {
                        $q->where('name', $product_type);
                    });
                }

                if ($discount_only) {
                    $productsQuery->where('discount', '>', 0);
                }

                if ($hasMinPrice || $hasMaxPrice) {
                    $productsQuery->whereBetween('price', [$min_price, $max_price]);
                }

                if (in_array($order_by, ['most_rated', 'rating'])) {
                    $productsQuery->orderBy('stars_rate', 'desc');
                } elseif ($order_by === 'latest') {
                    $productsQuery->orderBy('created_at', 'desc');
                } elseif ($order_by === 'price_low') {
                    $productsQuery->orderBy('price', 'asc');
                } elseif ($order_by === 'price_high') {
                    $productsQuery->orderBy('price', 'desc');
                }

                $total = $productsQuery->count();
                $models = $productsQuery->skip(($page - 1) * $limit)->take($limit)->get();

                $items = $models->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'stars_rate' => $product->stars_rate,
                        'discount' => $product->discount,
                        'shopID' => $product->shop_id,
                        'type' => $product->category?->name ?? '',
                        'image_url' => $product->primaryImage?->url ? asset('storage/' . $product->primaryImage->url) : '',
                        'category' => $product->category ? [
                            'id' => $product->category->id,
                            'name' => $product->category->name,
                            'name_en' => $product->category->name_en ?? $product->category->name,
                            'name_ar' => $product->category->name_ar ?? $product->category->name,
                            'icon' => $product->category->icon,
                        ] : null,
                        'shop' => $product->shop ? [
                            'name' => $product->shop->name,
                            'logo' => $product->shop->logo,
                        ] : null,
                    ];
                });

                return [
                    'items' => $items,
                    'total' => $total,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => (int) ceil($total / $limit),
                ];
            });

            return response()->json($response);

        } catch (\Throwable $e) {
            Log::error('[ProductsFilterController] Productfilter failed: ' . $e->getMessage(), [
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
