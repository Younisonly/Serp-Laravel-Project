<?php

namespace App\Http\Controllers;

use App\Helpers\FileUploadHelper;
use App\Models\Shop;
use App\Models\Phone;
use App\Rules\ImageAll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class shopRegisteration extends Controller
{
    public function store(Request $r)
    {
        try {
            $rules = [
                'name'               => 'required|string|max:120',
                'description'        => 'nullable|string|max:2000',
                'category'           => 'required|boolean',
                'iconname'           => 'nullable|string|max:60',
                'open_at'            => 'nullable|date_format:H:i',
                'close_at'           => 'nullable|date_format:H:i|after:open_at',
                'days_open'          => 'nullable|string|max:50',
                'address'            => 'nullable|string|max:255',
                'lat'                => 'nullable|numeric|between:-90,90',
                'lng'                => 'nullable|numeric|between:-180,180',
                'terms_accepted'     => 'required|accepted',
                'image'       => ['nullable', 'max:5120', new ImageAll()],
                'verification_file'  => 'nullable|max:5120',
                'contact_email'      => 'nullable|email|max:100',
                'website'            => 'nullable|url|max:200',
                'instagram'          => 'nullable|string|max:200',
                'X'                  => 'nullable|string|max:200',
                'whatsapp'           => 'nullable|string|max:200',
                'facebook'           => 'nullable|string|max:200',
            ];
//file|mimes:pdf,jpg,jpeg,png
            $validator = Validator::make($r->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Validation failed' . $validator->errors(),
                    'errors'  => $validator->errors(),
                ], 422);
            }


            DB::beginTransaction();
            $user = auth()->guard('sanctum')->user();

            $shop = Shop::create([
                'owner_id'          => $user->id,
                'name'              => $r->name,
                'description'       => $r->description,
                'category'          => $r->boolean('category'),
                'iconname'          => $r->iconname ?? 'default',
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

                'contact_email'     => $r->contact_email,
                'website'           => $r->website,
                'instagram'         => $r->instagram,
                'X'                 => $r->X,
                'whatsapp'          => $r->whatsapp,
                'facebook'          => $r->facebook,
            ]);
  // Save phone numbers (array of phones from request)
  if ($r->has('phones') && is_array($r->phones)) {
    foreach ($r->phones as $phoneNumber) {
        if (!empty($phoneNumber)) {
            Phone::create([
                'table' => 'shops',
                'owner_id' => $shop->id,
                'number' => $phoneNumber,
            ]);
        }
    }
}


            if ($r->hasFile('logo')) {

                $logoUrl = FileUploadHelper::upload(
                    $r,
                    'logo',
                    'shop',
                    $shop->id,
                    'shops',
                    true
                );
                $shop->update(['logo' => $logoUrl]);
            }
            // ✅ Upload verification file (stored automatically in DB by helper)
            if ($r->hasFile('verification_file')) {
                $verifUrl = FileUploadHelper::upload(
                    $r,
                    'verification_file',
                    'verification',
                    $shop->id,
                    'shops'
                );

                $shop->update(['verification_files' => [$verifUrl]]);
            }

///update user type to shop owner 
$user = \App\Models\User::find($user->id);
$user->type = 'servicePro';
$user->save();
            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Shop created – pending review.',
                'shop'    => $shop->load('images'),
                'user'    => $user->only(['id', 'name', 'email', 'phone', 'country', 'city', 'gender', 'birth_date', 'image','type']),

            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Server error',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
