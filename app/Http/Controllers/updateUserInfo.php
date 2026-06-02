<?php

namespace App\Http\Controllers;

use App\Helpers\FileUploadHelper;
use App\Http\Requests\UpdateUserInfoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class updateUserInfo extends Controller
{
  public function update(UpdateUserInfoRequest $r)
  {
    try {
        $validated = $r->validated();

        DB::beginTransaction();
        $user = auth()->guard('sanctum')->user();

        if ($r->filled('name'))       $user->name       = strip_tags($validated['name']);
        if ($r->filled('phone'))      $user->phone      = strip_tags($validated['phone']);
        if ($r->filled('country'))    $user->country    = strip_tags($validated['country']);
        if ($r->filled('city'))       $user->city       = strip_tags($validated['city']);
        if ($r->filled('gender'))     $user->gender     = $validated['gender'];
        if ($r->filled('birth_date')) $user->birth_date = Carbon::parse($validated['birth_date'])->format('Y-m-d');
        if ($r->filled('password'))   $user->password   = Hash::make($validated['password']);

        if ($r->hasFile('image')) {
            $file = $r->file('image');
            if ($file->getSize() > 5 * 1024 * 1024) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Image too large (max 5MB)'], 422);
            }
            $uploadedUrl = FileUploadHelper::upload($r, 'image', 'user', $user->id, 'users');
            if ($uploadedUrl) $user->image = $uploadedUrl;
        }

        $user->save();
        DB::commit();

        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user'    => $user->toArray(),
        ], 200);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('updateUserInfo failed', ['user_id' => auth()->id(), 'error' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => 'Server error, please try again',
        ], 500);
    }
  }
}
