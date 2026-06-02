<?php

namespace App\Http\Controllers;

use App\Helpers\FileUploadHelper;
use App\Http\Requests\UpdateShopRequest;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use App\Models\Phone;
use App\Events\NotificationTriggered;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopManagementController extends Controller
{
    /**
     * Get the authenticated user's shop with full details
     */
    public function myShop(Request $request)
    {
        try {
            $shop = Shop::where('owner_id', $request->user()->id)
                ->with(['images', 'primaryImage', 'phones'])
                ->first();

            if (!$shop) {
                return response()->json([
                    'success' => false,
                    'message' => 'No shop found for this user.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new ShopResource($shop),
            ]);
        } catch (\Exception $e) {
            Log::error('My shop fetch failed', ['user_id' => $request->user()?->id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Server error.',
            ], 500);
        }
    }

    /**
     * Update shop profile
     */
    public function updateShop(UpdateShopRequest $request, $id)
    {
        try {
            $shop = Shop::findOrFail($id);

            if ($shop->owner_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.',
                ], 403);
            }

            $validated = $request->validated();

            // Convert amenities from comma-separated string to array
            if ($request->has('amenities')) {
                $amenities = $request->amenities;
                if (is_string($amenities)) {
                    $validated['amenities'] = array_filter(array_map('trim', explode(',', $amenities)));
                }
            }

            // Update phones if provided
            if ($request->has('phones')) {
                $phones = $request->phones;
                if (is_string($phones)) {
                    $phones = array_filter(array_map('trim', explode(',', $phones)));
                }
                Phone::where('table', 'shops')->where('owner_id', $shop->id)->delete();
                if (is_array($phones)) {
                    foreach ($phones as $phoneNumber) {
                        if (!empty($phoneNumber)) {
                            Phone::create([
                                'table' => 'shops',
                                'owner_id' => $shop->id,
                                'number' => trim($phoneNumber),
                            ]);
                        }
                    }
                }
            }

            // Upload logo if provided
            if ($request->hasFile('logo')) {
                $logoUrl = FileUploadHelper::upload($request, 'logo', 'shop', $shop->id, 'shops', true);
                $validated['logo'] = $logoUrl;
                unset($validated['logo']); // Remove from validated to avoid double handling
                $shop->update(['logo' => $logoUrl]);
            }

            // Upload backlogo if provided
            if ($request->hasFile('backlogo')) {
                $backlogoUrl = FileUploadHelper::upload($request, 'backlogo', 'shop', $shop->id, 'shops', false);
                $shop->update(['backlogo' => $backlogoUrl]);
            }

            // Remove file fields and phones from validated data
            unset($validated['logo'], $validated['backlogo'], $validated['phones']);

            // Update remaining fields
            if (!empty($validated)) {
                $shop->update($validated);
            }

            try {
                NotificationTriggered::dispatch('shop_updated', [
                    'shop' => $shop,
                    'user_name' => $request->user()->name,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Shop updated notification skipped: ' . $e->getMessage());
            }

            $shop->refresh()->load(['images', 'primaryImage', 'phones']);

            return response()->json([
                'success' => true,
                'message' => 'Shop updated successfully.',
                'data' => new ShopResource($shop),
            ]);
        } catch (\Exception $e) {
            Log::error('Shop update failed', ['shop_id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Server error.',
            ], 500);
        }
    }

    /**
     * Update working hours
     */
    public function updateHours(Request $request, $id)
    {
        try {
            $shop = Shop::findOrFail($id);

            if ($shop->owner_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $validated = $request->validate([
                'open_at' => 'nullable|date_format:H:i',
                'close_at' => 'nullable|date_format:H:i',
                'days_open' => 'nullable|string|max:255',
            ]);

            $shop->update($validated);

            try {
                NotificationTriggered::dispatch('shop_status_changed', [
                    'shop' => $shop,
                    'status' => ($shop->is_open ?? true) ? 'open' : 'closed',
                ]);
            } catch (\Throwable $e) {
                Log::warning('Shop status notification skipped: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Working hours updated.',
                'data' => [
                    'open_at' => $shop->open_at?->format('H:i'),
                    'close_at' => $shop->close_at?->format('H:i'),
                    'days_open' => $shop->days_open ? explode(',', $shop->days_open) : [],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Update hours failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    /**
     * Delete shop (soft approach - marks as suspended)
     */
    public function deleteShop(Request $request, $id)
    {
        try {
            $shop = Shop::findOrFail($id);

            if ($shop->owner_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $validated = $request->validate([
                'reason' => 'nullable|string|max:500',
            ]);

            $shop->update([
                'status' => 'closed',
                'suspended_at' => now(),
                'suspension_reason' => $validated['reason'] ?? 'Deleted by owner',
            ]);

            try {
                NotificationTriggered::dispatch('shop_suspended', [
                    'shop' => $shop,
                    'reason' => $validated['reason'] ?? 'Deleted by owner',
                    'owner_id' => $shop->owner_id,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Shop suspension notification skipped: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Shop has been deactivated.',
            ]);
        } catch (\Exception $e) {
            Log::error('Delete shop failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    /**
     * Get shop verification status
     */
    public function verificationStatus(Request $request, $id)
    {
        try {
            $shop = Shop::findOrFail($id);

            if ($shop->owner_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $shop->verification_status,
                    'note' => $shop->verification_note,
                    'reviewed_at' => $shop->reviewed_at?->toISOString(),
                    'suspended_at' => $shop->suspended_at?->toISOString(),
                    'suspension_reason' => $shop->suspension_reason,
                    'verification_files' => $shop->verification_files,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Verification status failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }
}
