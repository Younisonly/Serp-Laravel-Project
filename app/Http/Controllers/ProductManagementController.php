<?php

namespace App\Http\Controllers;

use App\Events\NotificationTriggered;
use App\Helpers\FileUploadHelper;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProductManagementController extends Controller
{
    /**
     * List all products for the authenticated user's shop
     */
    public function index(Request $request, $shopId)
    {
        try {
            $shop = Shop::findOrFail($shopId);

            if ($shop->owner_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $perPage = min((int) $request->get('per_page', 20), 50);
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $categoryId = $request->get('category_id');
            $status = $request->get('status'); // active/discounted

            $query = Product::where('shop_id', $shopId)
                ->with(['primaryImage', 'category'])
                ->withCount(['favorites', 'likes']);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('brand', 'like', "%{$search}%");
                });
            }

            if ($categoryId) {
                $query->where('type_id', $categoryId);
            }

            if ($status === 'discounted') {
                $query->where('discount', '>', 0);
            }

            $products = $query->latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($products),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('List products failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    /**
     * Create a new product
     */
    public function store(StoreProductRequest $request, $shopId)
    {
        try {
            $shop = Shop::findOrFail($shopId);

            if ($shop->owner_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $validated = $request->validated();

            // Decode JSON string specifications to array for model cast
            if (isset($validated['specifications']) && is_string($validated['specifications'])) {
                $decoded = json_decode($validated['specifications'], true);
                if (is_array($decoded)) {
                    $validated['specifications'] = $decoded;
                }
            }

            DB::beginTransaction();

            $product = Product::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'discount' => $validated['discount'] ?? 0,
                'discount_start' => $validated['discount_start'] ?? null,
                'discount_end' => $validated['discount_end'] ?? null,
                'brand' => $validated['brand'] ?? null,
                'model_number' => $validated['model_number'] ?? null,
                'color' => $validated['color'] ?? null,
                'size' => $validated['size'] ?? null,
                'material' => $validated['material'] ?? null,
                'weight' => $validated['weight'] ?? null,
                'barcode' => $validated['barcode'] ?? null,
                'warranty' => $validated['warranty'] ?? false,
                'warranty_start' => $validated['warranty_start'] ?? null,
                'warranty_end' => $validated['warranty_end'] ?? null,
                'warranty_description' => $validated['warranty_description'] ?? null,
                'highlights' => $validated['highlights'] ?? null,
                'specifications' => $validated['specifications'] ?? null,
                'tags' => $validated['tags'] ?? null,
                'shipping_info' => $validated['shipping_info'] ?? null,
                'return_policy' => $validated['return_policy'] ?? null,
                'delivery_info' => $validated['delivery_info'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'type_id' => $validated['type_id'] ?? null,
                'shop_id' => $shopId,
            ]);

            // Handle product images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $tempRequest = new Request();
                    $tempRequest->files->set('product_image_' . $index, $file);

                    $imageUrl = FileUploadHelper::upload(
                        $tempRequest,
                        'product_image_' . $index,
                        'product',
                        $product->id,
                        'products',
                        $index === 0
                    );

                    if ($imageUrl) {
                        Image::create([
                            'table' => 'products',
                            'owner_id' => $product->id,
                            'url' => str_replace('/storage/', '', $imageUrl),
                            'is_primary' => $index === 0,
                            'status' => 'active',
                        ]);
                    }
                }
            }

            $product->load(['primaryImage', 'images', 'category']);

            DB::commit();

            try {
                NotificationTriggered::dispatch('product_added', [
                    'shop' => $shop,
                    'product_name' => $product->name,
                    'product_id' => $product->id,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Product added notification skipped: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully.',
                'data' => new ProductResource($product),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create product failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single product
     */
    public function show(Request $request, $shopId, $productId)
    {
        try {
            $shop = Shop::findOrFail($shopId);

            if ($shop->owner_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $product = Product::where('shop_id', $shopId)
                ->withCount(['favorites', 'likes'])
                ->findOrFail($productId)
                ->load(['primaryImage', 'images', 'category']);

            return response()->json([
                'success' => true,
                'data' => new ProductResource($product),
            ]);
        } catch (\Exception $e) {
            Log::error('Show product failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    /**
     * Update a product
     */
    public function update(UpdateProductRequest $request, $shopId, $productId)
    {
        try {
            $shop = Shop::findOrFail($shopId);

            if ($shop->owner_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $product = Product::where('shop_id', $shopId)->findOrFail($productId);

            $validated = $request->validated();

            // Decode JSON string specifications to array for model cast
            if (isset($validated['specifications']) && is_string($validated['specifications'])) {
                $decoded = json_decode($validated['specifications'], true);
                if (is_array($decoded)) {
                    $validated['specifications'] = $decoded;
                }
            }

            // Remove images from validated (handled separately)
            $images = $validated['images'] ?? null;
            unset($validated['images']);

            $product->update($validated);

            // Handle new images
            if ($images) {
                foreach ($images as $index => $file) {
                    $tempRequest = new Request();
                    $tempRequest->files->set('product_image_' . $index, $file);

                    $imageUrl = FileUploadHelper::upload(
                        $tempRequest,
                        'product_image_' . $index,
                        'product',
                        $product->id,
                        'products',
                        $product->images()->count() === 0
                    );

                    if ($imageUrl) {
                        Image::create([
                            'table' => 'products',
                            'owner_id' => $product->id,
                            'url' => str_replace('/storage/', '', $imageUrl),
                            'is_primary' => $product->images()->count() === 0,
                            'status' => 'active',
                        ]);
                    }
                }
            }

            $product->load(['primaryImage', 'images', 'category']);

            try {
                NotificationTriggered::dispatch('product_updated', [
                    'shop_id' => $shop->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'shop' => $shop,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Product updated notification skipped: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully.',
                'data' => new ProductResource($product),
            ]);
        } catch (\Exception $e) {
            Log::error('Update product failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a product
     */
    public function destroy(Request $request, $shopId, $productId)
    {
        try {
            $shop = Shop::findOrFail($shopId);

            if ($shop->owner_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $product = Product::where('shop_id', $shopId)->findOrFail($productId);

            // Delete associated images from storage
            foreach ($product->images as $image) {
                $path = public_path($image->url);
                if (file_exists($path)) {
                    @unlink($path);
                }
                $image->delete();
            }

            $product->delete();

            try {
                NotificationTriggered::dispatch('product_deleted', [
                    'shop_id' => $shop->id,
                    'product_id' => (int) $productId,
                    'product_name' => $product->name,
                    'shop' => $shop,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Product deleted notification skipped: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Delete product failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk delete products
     */
    public function bulkDelete(Request $request, $shopId)
    {
        try {
            $shop = Shop::findOrFail($shopId);

            if ($shop->owner_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $validated = $request->validate([
                'product_ids' => 'required|array|min:1',
                'product_ids.*' => 'integer|exists:products,id',
            ]);

            $count = Product::where('shop_id', $shopId)
                ->whereIn('id', $validated['product_ids'])
                ->delete();

            return response()->json([
                'success' => true,
                'message' => "{$count} product(s) deleted.",
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk delete failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    /**
     * Get product statistics
     */
    public function stats(Request $request, $shopId)
    {
        try {
            $shop = Shop::findOrFail($shopId);

            if ($shop->owner_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $totalProducts = Product::where('shop_id', $shopId)->count();
            $discountedProducts = Product::where('shop_id', $shopId)->where('discount', '>', 0)->count();
            $avgPrice = Product::where('shop_id', $shopId)->avg('price');
            $avgRating = Product::where('shop_id', $shopId)->where('raters_number', '>', 0)->avg('stars_rate');

            // Products by category
            $productsByCategory = Product::where('shop_id', $shopId)
                ->selectRaw('type_id, COUNT(*) as count')
                ->groupBy('type_id')
                ->get()
                ->map(function ($item) {
                    $category = \App\Models\ProductCategory::find($item->type_id);
                    return [
                        'category_id' => $item->type_id,
                        'category_name' => $category?->name ?? 'Uncategorized',
                        'count' => $item->count,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'total_products' => $totalProducts,
                    'discounted_products' => $discountedProducts,
                    'average_price' => round($avgPrice ?? 0, 2),
                    'average_rating' => round($avgRating ?? 0, 2),
                    'products_by_category' => $productsByCategory,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Product stats failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }
}
