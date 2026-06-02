<?php

namespace App\Http\Controllers;

use App\Helpers\FileUploadHelper;
use App\Http\Resources\ImageResource;
use App\Models\Shop;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageManagementController extends Controller
{
    /**
     * Get all shop images
     */
    public function index(Request $request, $shopId)
    {
        try {
            $shop = Shop::findOrFail($shopId);

            if ($shop->owner_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $images = Image::where('table', 'shops')
                ->where('owner_id', $shopId)
                ->orderBy('is_primary', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => ImageResource::collection($images),
            ]);
        } catch (\Exception $e) {
            Log::error('List shop images failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    /**
     * Upload new shop images
     */
    public function upload(Request $request, $shopId)
    {
        try {
            $shop = Shop::findOrFail($shopId);

            if ($shop->owner_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $request->validate([
                'images' => 'required|array|max:10',
                'images.*' => 'image|max:5120',
            ]);

            $uploaded = [];

            foreach ($request->file('images') as $index => $file) {
                $tempRequest = new Request();
                $tempRequest->files->set('shop_image_' . $index, $file);

                $imageUrl = FileUploadHelper::upload(
                    $tempRequest,
                    'shop_image_' . $index,
                    'shop',
                    $shop->id,
                    'shops',
                    false
                );

                if ($imageUrl) {
                    $image = Image::create([
                        'table' => 'shops',
                        'owner_id' => $shop->id,
                        'url' => str_replace('/storage/', '', $imageUrl),
                        'is_primary' => false,
                        'status' => 'active',
                    ]);
                    $uploaded[] = $image;
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($uploaded) . ' image(s) uploaded.',
                'data' => ImageResource::collection(collect($uploaded)),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Upload shop images failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    /**
     * Set an image as primary
     */
    public function setPrimary(Request $request, $shopId, $imageId)
    {
        try {
            $shop = Shop::findOrFail($shopId);

            if ($shop->owner_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $image = Image::where('table', 'shops')
                ->where('owner_id', $shopId)
                ->findOrFail($imageId);

            // Unset all primary images
            Image::where('table', 'shops')
                ->where('owner_id', $shopId)
                ->update(['is_primary' => false]);

            // Set this one as primary
            $image->update(['is_primary' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Primary image updated.',
                'data' => new ImageResource($image),
            ]);
        } catch (\Exception $e) {
            Log::error('Set primary image failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    /**
     * Delete a shop image
     */
    public function destroy(Request $request, $shopId, $imageId)
    {
        try {
            $shop = Shop::findOrFail($shopId);

            if ($shop->owner_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $image = Image::where('table', 'shops')
                ->where('owner_id', $shopId)
                ->findOrFail($imageId);

            // Delete file from storage
            $path = public_path($image->url);
            if (file_exists($path)) {
                @unlink($path);
            }

            $image->delete();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted.',
            ]);
        } catch (\Exception $e) {
            Log::error('Delete shop image failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    /**
     * Reorder images (set new order via array of IDs)
     */
    public function reorder(Request $request, $shopId)
    {
        try {
            $shop = Shop::findOrFail($shopId);

            if ($shop->owner_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $validated = $request->validate([
                'image_ids' => 'required|array',
                'image_ids.*' => 'integer',
            ]);

            foreach ($validated['image_ids'] as $index => $imageId) {
                Image::where('table', 'shops')
                    ->where('owner_id', $shopId)
                    ->where('id', $imageId)
                    ->update(['is_primary' => $index === 0]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Images reordered.',
            ]);
        } catch (\Exception $e) {
            Log::error('Reorder images failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }
}
