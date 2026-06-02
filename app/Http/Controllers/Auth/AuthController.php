<?php

namespace App\Http\Controllers\Auth;

use App\Events\NotificationTriggered;
use App\Helpers\FileUploadHelper;
use App\Helpers\HashHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    private function formatUserResponse(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
            'country' => $user->country ?? '',
            'city' => $user->city ?? '',
            'gender' => $user->gender ?? '',
            'birth_date' => $user->birth_date ?? '',
            'image' => $user->image ?? '',
            'status' => $user->status ?? 'active',
            'type' => $user->type ?? 'user',
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    public function register(RegisterRequest $request)
    {
        try {
            $validated = $request->validated();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => HashHelper::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'status' => 'active',
                'type' => 'user',
                'country' => null,
                'city' => null,
                'gender' => null,
                'birth_date' => null,
            ]);

            $imageUrl = FileUploadHelper::upload($request, 'image', 'user', $user->id, 'users');
            if ($imageUrl) {
                $user->update(['image' => $imageUrl]);
            }

            $token = $user->createToken('auth_token', ['*'], now()->addDays(30))->plainTextToken;

            try {
                NotificationTriggered::dispatch('welcome_user', [
                    'user' => $user,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Welcome notification skipped: ' . $e->getMessage());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'تم التسجيل بنجاح',
                'user' => $this->formatUserResponse($user),
                'token' => $token,
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Registration failed', [
                'email' => $request->email,
                'ip' => $request->ip(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $validated = $request->validated();

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                Log::warning('Failed login attempt', [
                    'email' => $validated['email'],
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'status' => 'error',
                    'error' => 'Invalid email or password.',
                ], 401);
            }

            if ($user->status !== 'active') {
                Log::warning('Inactive user login attempt', [
                    'user_id' => $user->id,
                    'status' => $user->status,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'status' => 'error',
                    'error' => 'Account is not active.',
                ], 403);
            }

            $token = $user->createToken('auth_token', ['*'], now()->addDays(30))->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'تم تسجيل الدخول',
                'user' => $this->formatUserResponse($user),
                'token' => $token,
            ]);
        } catch (\Throwable $e) {
            Log::error('Login failed', [
                'email' => $request->email,
                'ip' => $request->ip(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'تم تسجيل الخروج بنجاح',
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Logout failed', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated'
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'user' => $this->formatUserResponse($user),
        ], 200);
    }
}