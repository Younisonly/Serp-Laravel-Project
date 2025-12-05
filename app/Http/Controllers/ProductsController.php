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


            $products = Product::select(
                'products.id',
                'products.name',
                'products.price',
                'products.created_at',
                'images.url as primary_image',
                'discount'
            )
            ->leftJoin('images', function ($join) {
                $join->on('images.owner_id', '=', 'products.id')
                     ->where('images.table', '=', 'products')
                     ->where('images.is_primary', '=', 1);
            })
            ->where('products.shop_id', $shop_id)
            ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'status' => 'success',
                'data' => $products
            ]);
        } catch (\Throwable $e) {
            Log::error('ProductsOfShop Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong while fetching products.',
                'exception' => get_class($e),
                'error_message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
    

    public function getProductById($id)
    {
        try {
            // product + images + category + mini-shop
            $product = Product::with(['primaryImage', 'images', 'category'])
                ->with(['shop' => function ($q) {
                    // only the fields you need
                    $q->select('id', 'name', 'address', 'logo');
                }])
                ->find($id);
    
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }
    
            return response()->json([
                'success' => true,
                'data'    => $product   // ← product.shop.id / .name / .address / .logo
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    

}
