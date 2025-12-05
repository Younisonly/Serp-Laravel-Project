<?php

namespace App\Http\Controllers;

use App\Helpers\FileUploadHelper;
use App\Helpers\HashHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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

                if (!$hasOld && !$hasNew) {           // nothing sent – skip
                    return;
                }
                if ($hasOld && !$hasNew) {            // only old
                    $v->errors()->add('password', 'Please supply a new password.');
                    return;
                }
                if (!$hasOld && $hasNew) {            // only new
                    $v->errors()->add('old_password', 'Please supply the current password.  ');
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
                        /** @var User $user */

            $user = auth()->guard('sanctum')->user();

            $user->name       = $r->name;
            $user->phone      = $r->phone;
            $user->country    = $r->country;
            $user->city       = $r->city;
            $user->gender     = $r->gender;
            $user->birth_date = Carbon::parse($r->birth_date)->format('Y-m-d');

            if ($r->filled('password')) {                 // old already verified
                $user->password = HashHelper::make($r->password);
            }

            if ($r->hasFile('image')) {
                $user->image = FileUploadHelper::upload($r, 'image', 'user', $user->id, 'users');
            }

            $user->save();
            DB::commit();

            /* ----------  exact shape Flutter prints  ---------- */
            return response()->json([
                'status'  => 'success',
                'message' => 'Profile updated',
                'user'    => $user->only([
                    'id', 'name', 'email', 'phone', 'country', 'city',
                    'gender', 'birth_date', 'image', 'type'
                ]),
            ], 200);

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