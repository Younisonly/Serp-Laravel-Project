<?php

namespace App\Http\Controllers;

use App\Helpers\FileUploadHelper;
use App\Helpers\HashHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Rules\ImageAll;
use Illuminate\Support\Facades\Hash;

class updateUserInfo extends Controller
{
    public function update(Request $r)
    {
        try {
            /* ----------  base rules  ---------- */
            $rules = [
                'name'        => 'required|string|max:120',
                'phone'       => 'required|string|max:20|min:9',
                'country'     => 'required|string|max:60',
                'city'        => 'required|string|max:60',
                'gender'      => 'required|in:male,female',
                'birth_date'  => 'required|date|before:today',
                'image'       => ['nullable', 'max:5120', new ImageAll()],
                'old_password'=> 'nullable|string',
                'password'    => 'nullable|string|min:6',
            ];

            $validator = Validator::make($r->all(), $rules);

            /* ----------  password custom logic  ---------- */
            $validator->after(function (\Illuminate\Validation\Validator $v) use ($r) {
                $hasOld = $r->filled('old_password');
                $hasNew = $r->filled('password');

                if (!$hasOld && !$hasNew) { return; }
                if ($hasOld && !$hasNew) {
                    $v->errors()->add('password', 'Please supply a new password.');
                    return;
                }
                if (!$hasOld && $hasNew) {
                    $v->errors()->add('old_password', 'Please supply the current password.');
                    return;
                }
                if (!Hash::check($r->old_password, auth()->guard('sanctum')->user()->password)) {
                    $v->errors()->add('old_password', 'Old password is incorrect.');
                }
            });

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            /* ----------  update user  ---------- */
            DB::beginTransaction();
            $user = auth()->guard('sanctum')->user();
            
            // ✅ Save current image as fallback
            $currentImage = $user->image;

            $user->name       = $r->name;
            $user->phone      = $r->phone;
            $user->country    = $r->country;
            $user->city       = $r->city;
            $user->gender     = $r->gender;
            $user->birth_date = Carbon::parse($r->birth_date)->format('Y-m-d');

            if ($r->filled('password')) {
                $user->password = HashHelper::make($r->password);
            }

            // ✅ FIXED: Handle image upload with logging & fallback
            if ($r->hasFile('image')) {
                Log::info('Image upload attempt', [
                    'user_id' => $user->id,
                    'file_name' => $r->file('image')->getClientOriginalName(),
                    'file_size' => $r->file('image')->getSize(),
                ]);

                $uploadedUrl = FileUploadHelper::upload($r, 'image', 'user', $user->id, 'users');
                
                if ($uploadedUrl) {
                    $user->image = $uploadedUrl;
                    Log::info('Image upload success', ['url' => $uploadedUrl]);
                } else {
                    // ✅ Keep old image if upload fails
                    $user->image = $currentImage;
                    Log::warning('Image upload returned null - keeping existing image', [
                        'user_id' => $user->id
                    ]);
                }
            }

            $user->save();
            DB::commit();

            // ✅ Return explicit user data (guarantee image field)
            return response()->json([
                'status'  => 'success',
                'message' => 'Profile updated',
                'user'    => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'country' => $user->country,
                    'city' => $user->city,
                    'gender' => $user->gender,
                    'birth_date' => $user->birth_date,
                    'image' => $user->image,  // ✅ Explicitly include
                    'type' => $user->type,
                ],
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('updateUserInfo failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status'  => 'error',
                'message' => 'Server error',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}