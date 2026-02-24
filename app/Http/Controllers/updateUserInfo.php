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
        // ✅ Validation rules
        $rules = [
            'name'        => 'required|string|max:120',
            'phone'       => 'required|string|max:20|min:9',
            'country'     => 'required|string|max:60',
            'city'        => 'required|string|max:60',
            'gender'      => 'required|in:male,female',
            'birth_date'  => 'required|date|before:today',
            'image'       => ['nullable', 'max:5120', new ImageAll()], // ✅ Max 5MB
            'old_password'=> 'nullable|string',
            'password'    => 'nullable|string|min:6',
        ];

        $validator = Validator::make($r->all(), $rules);

        // ✅ Password logic
        $validator->after(function (\Illuminate\Validation\Validator $v) use ($r) {
            $hasOld = $r->filled('old_password');
            $hasNew = $r->filled('password');
            if (!$hasOld && !$hasNew) return;
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
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        $user = auth()->guard('sanctum')->user();
        $currentImage = $user->image;

        // ✅ Update basic fields
        $user->name       = $r->name;
        $user->phone      = $r->phone;
        $user->country    = $r->country;
        $user->city       = $r->city;
        $user->gender     = $r->gender;
        $user->birth_date = \Illuminate\Support\Carbon::parse($r->birth_date)->format('Y-m-d');
        if ($r->filled('password')) {
            $user->password = \App\Helpers\HashHelper::make($r->password);
        }

        // ✅ SAFE Image Upload with Error Handling
        if ($r->hasFile('image')) {
            $file = $r->file('image');
            
            // 🔍 Pre-validation: Check size BEFORE processing
            if ($file->getSize() > 5 * 1024 * 1024) { // 5MB
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'حجم الصورة كبير جداً، الرجاء اختيار صورة أقل من 5 ميجابايت'
                ], 422);
            }
            
            // 🔍 Check MIME type
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!in_array($file->getMimeType(), $allowedMimes)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'نوع الصورة غير مدعوم، الرجاء استخدام JPG, PNG, أو WebP'
                ], 422);
            }

            try {
                // 🔥 Attempt upload with timeout protection
                $uploadedUrl = @\App\Helpers\FileUploadHelper::upload($r, 'image', 'user', $user->id, 'users');
                
                if ($uploadedUrl) {
                    $user->image = $uploadedUrl;
                } else {
                    // Keep old image if upload fails silently
                    $user->image = $currentImage;
                    \Illuminate\Support\Facades\Log::warning('Image upload returned null', ['user_id' => $user->id]);
                }
            } catch (\Throwable $uploadError) {
                DB::rollBack();
                \Illuminate\Support\Facades\Log::error('Image upload exception: ' . $uploadError->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'فشل معالجة الصورة، الرجاء المحاولة بصيغة أخرى أو حجم أصغر'
                ], 500);
            }
        }

        $user->save();
        DB::commit();

        // ✅ Return full user object
        return response()->json([
            'success' => true,
            'message' => 'تم حفظ التعديلات بنجاح',
            'user'    => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'country' => $user->country,
                'city' => $user->city,
                'gender' => $user->gender,
                'birth_date' => $user->birth_date,
                'image' => $user->image,
                'type' => $user->type,
            ],
        ], 200);

    } catch (\Throwable $e) {
        DB::rollBack();
        \Illuminate\Support\Facades\Log::error('updateUserInfo failed: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ في الخادم، الرجاء المحاولة لاحقاً',
            'error'   => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
}
}