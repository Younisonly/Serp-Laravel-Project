<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductCategory;

class ProductsFillterController extends Controller
{
    public function Productfilter(Request $request)
    {
        try {
            $request->validate([
                'query' => 'nullable|string|max:255',
                'product_type' => 'nullable|string',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0',
                'discount_only' => 'nullable|string|in:true,false,1,0',
                'order_by' => 'nullable|string|in:most_rated,latest',
                'page' => 'nullable|integer|min:1',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);

            $query = $request->input('query', '');
            $product_type = $request->input('product_type', '');
            $min_price = $request->input('min_price', 0);
            $max_price = $request->input('max_price', 500000);
            $order_by = $request->input('order_by', '');
            $limit = $request->input('limit', 10);
            $discount_only = filter_var($request->input('discount_only', false), FILTER_VALIDATE_BOOLEAN);

            $productsQuery = Product::with(['primaryImage', 'city', 'category']);

            if ($query !== '' && $query !== 'all') {
                $productsQuery->where('name', 'like', "%{$query}%");
            }

            
            if ($product_type !== '') {
                $productsQuery->whereHas('category', function ($q) use ($product_type) {
                    $q->where('name', $product_type);
                });
            }

            if ($discount_only) {
                $productsQuery->where('discount','>',0 );
            }

            $productsQuery->whereBetween('price', [$min_price, $max_price]);

            if ($order_by === 'most_rated') {
                $productsQuery->orderBy('stars_rate', 'desc');
            } elseif ($order_by === 'latest') {
                $productsQuery->orderBy('created_at', 'desc');
            }

            $products = $productsQuery->paginate($limit);

            $products->getCollection()->transform(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'stars_rate' => $product->stars_rate,
                    'discount' => $product->discount,
                    'shopID' => $product->shop_id,
                    'type' => $product->category->name ?? '',
                    'image_url' => $product->primaryImage?->url ?? '',
                ];
            });

            return response()->json($products);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
                'error_message' => $e->getMessage(),
            ], 500);
        }
    }
}
