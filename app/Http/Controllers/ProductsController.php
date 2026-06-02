<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductsController extends Controller
{
    public function productsOfShop(Request $request)
    {
        try {
            $shop_id = $request->input('shop_id');
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);
    
            if (!$shop_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'shop_id query parameter is required.'
                ], 400);
            }

            $products = Product::with(['shop:id,name,logo', 'primaryImage'])
                ->where('shop_id', $shop_id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $products->getCollection()->transform(function ($product) {
                $product->primary_image = $product->primaryImage?->url ? asset('storage/' . $product->primaryImage->url) : null;
                $product->makeHidden(['primaryImage', 'images']);
                return $product;
            });

            return response()->json([
                'status' => 'success',
                'data' => $products->items(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[ProductsController] productsOfShop failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }
    
    public function getProductById($id)
    {
        try {
            $product = Product::with(['primaryImage', 'images', 'category'])
                ->with(['shop' => function ($q) {
                    $q->select('id', 'name', 'address', 'logo');
                }])
                ->withCount(['favorites', 'likes'])
                ->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $product->primary_image = $product->primaryImage?->url ? asset('storage/' . $product->primaryImage->url) : null;
            $product->images_list = $product->images->map(fn($i) => $i->url ? asset('storage/' . $i->url) : null)->filter()->values()->toArray();
            $product->type = $product->category?->name;
            $product->likes_count = $product->likes_count ?? 0;
            $product->favorites_count = $product->favorites_count ?? 0;
            $product->makeHidden(['primaryImage', 'images', 'category']);

            return response()->json([
                'success' => true,
                'data'    => $product
            ], 200);

        } catch (\Exception $e) {
            Log::error('[ProductsController] getProductById failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }
    
    
}
