<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        try {
            $users = User::select('id', 'name', 'email', 'image', 'type', 'status')->paginate(20);
            return response()->json([
                'success' => true,
                'data' => $users->items(),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('UserController index failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8|max:128',
            ]);

            $validated['password'] = Hash::make($validated['password']);
            $user = User::create($validated);

            return response()->json([
                'success' => true,
                'data' => $user->only(['id', 'name', 'email', 'type', 'status']),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error('UserController store failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function show(User $user)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $user->only(['id', 'name', 'email', 'image', 'type', 'status']),
            ]);
        } catch (\Throwable $e) {
            Log::error('UserController show failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function update(Request $request, User $user)
    {
        try {
            if ((int) $request->user()?->id !== (int) $user->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,'.$user->id,
                'password' => 'sometimes|string|min:8|max:128',
            ]);

            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            $user->update($validated);
            $user->refresh();

            return response()->json([
                'success' => true,
                'data' => $user->only(['id', 'name', 'email', 'image', 'type', 'status']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            Log::error('UserController update failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function destroy(Request $request, User $user)
    {
        try {
            if ((int) $request->user()?->id !== (int) $user->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $user->delete();
            return response()->json(null, 204);
        } catch (\Throwable $e) {
            Log::error('UserController destroy failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function flutterUserInfo(Request $request, $id)
    {
        try {
            $authenticatedUserId = $request->user()?->id;
            if ((int) $authenticatedUserId !== (int) $id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ], 403);
            }

            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ], 404);
            }

            $data = $user->only([
                'id', 'name', 'email', 'phone', 'country', 'city',
                'gender', 'birth_date', 'image', 'type'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User retrieved successfully',
                'data' => $data,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('UserController flutterUserInfo failed', ['user_id' => $id, 'error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }
}
