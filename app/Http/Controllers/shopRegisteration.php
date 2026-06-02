<?php

namespace App\Http\Controllers;

use App\Events\NotificationTriggered;
use App\Helpers\FileUploadHelper;
use App\Models\Shop;
use App\Models\Phone;
use App\Rules\ImageAll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class shopRegisteration extends Controller
{
    public function store(Request $r)
    {
        try {
            $rules = [
                'name'               => 'required|string|max:120',
                'description'        => 'nullable|string|max:2000',
                'business_type'      => 'required|string|max:60',
                'iconname'           => 'nullable|string|max:60',
                'open_at'            => 'required|date_format:H:i',
                'close_at'           => 'required|date_format:H:i|after:open_at',
                'days_open'          => 'required|string|max:50',
                'address'            => 'nullable|string|max:255',
                'lat'                => 'required|numeric|between:-90,90',
                'lng'                => 'required|numeric|between:-180,180',
                'terms_accepted'     => 'required|accepted',
                'logo'               => ['nullable', 'max:5120', new ImageAll()],
                'cover'              => ['nullable', 'max:5120', new ImageAll()],
                'verification_files' => 'nullable|array|max:5',
                'verification_files.*' => ['nullable', 'max:5120', new ImageAll()],
                'phones'             => 'nullable|array|max:3',
                'email'              => 'nullable|email|max:100',
                'contact_email'      => 'nullable|email|max:100',
                'website'            => 'nullable|url|max:200',
                'instagram'          => 'nullable|string|max:200',
                'X'                  => 'nullable|string|max:200',
                'whatsapp'           => 'nullable|string|max:200',
                'facebook'           => 'nullable|string|max:200',
                'price_range'        => 'nullable|string|max:10',
                'amenities'          => 'nullable|string|max:500',
            ];

            $validator = Validator::make($r->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();
            $user = auth()->guard('sanctum')->user();

            $shop = Shop::create([
                'owner_id'          => $user->id,
                'name'              => $r->name,
                'description'       => $r->description,
                'business_type'     => $r->business_type ?? 'other',
                'iconname'          => $r->iconname ?? ($r->business_type ?? 'default'),
                'open_at'           => $r->open_at,
                'close_at'          => $r->close_at,
                'days_open'         => $r->days_open,
                'address'           => $r->address,
                'lat'               => $r->lat,
                'lng'               => $r->lng,
                'joined_at'         => now(),
                'terms_accepted_at' => now(),
                'terms_ip'          => $r->ip(),
                'verification_status' => 'pending',
                'price_range'       => $r->price_range,
                'amenities'         => $r->amenities ? explode(',', $r->amenities) : null,

                'contact_email'     => $r->email ?? $r->contact_email,
                'website'           => $r->website,
                'instagram'         => $r->instagram,
                'X'                 => $r->X,
                'whatsapp'          => $r->whatsapp,
                'facebook'          => $r->facebook,
            ]);

            // Save phone numbers
            $phones = $r->phones;
            if (is_string($phones)) {
                $phones = array_filter(array_map('trim', explode(',', $phones)));
            }
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

            // Upload logo
            if ($r->hasFile('logo')) {
                $logoUrl = FileUploadHelper::upload($r, 'logo', 'logo', $shop->id, 'shops', true);
                $shop->update(['logo' => $logoUrl]);
            }

            // Upload cover
            if ($r->hasFile('cover')) {
                $coverUrl = FileUploadHelper::upload($r, 'cover', 'cover', $shop->id, 'shops', true);
                $shop->update(['backlogo' => $coverUrl]);
            }

            // Upload verification files
            if ($r->hasFile('verification_files')) {
                $verifUrls = [];
                foreach ($r->file('verification_files') as $file) {
                    $url = FileUploadHelper::uploadSingleFile($file, 'verification', $shop->id, 'shops');
                    if ($url) $verifUrls[] = $url;
                }
                if (!empty($verifUrls)) {
                    $shop->update(['verification_files' => $verifUrls]);
                }
            } elseif ($r->hasFile('verification_file')) {
                $verifUrl = FileUploadHelper::upload($r, 'verification_file', 'verification', $shop->id, 'shops');
                $shop->update(['verification_files' => [$verifUrl]]);
            }

            // Update user type
            $user->type = 'servicePro';
            $user->save();

            DB::commit();

            try {
                NotificationTriggered::dispatch('shop_registered', [
                    'shop' => $shop,
                    'owner_id' => $user->id,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Shop registration notification skipped: ' . $e->getMessage());
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Shop created – pending review.',
                'shop'    => $shop->load('images'),
                'user'    => $user->only(['id', 'name', 'email', 'phone', 'country', 'city', 'gender', 'birth_date', 'image', 'type']),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Server error: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
