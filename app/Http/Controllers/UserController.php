<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // GET all users
    public function index()
    {
        return response()->json(User::all());
    }

    // POST new user
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create($validated);
        return response()->json($user, 201);
    }

    // GET single user
    public function show(User $user)
    {
        return response()->json($user);
    }

    // UPDATE user
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$user->id,
            'password' => 'sometimes|string|min:6',
        ]);

        $user->update($validated);
        return response()->json($user);
    }

    // DELETE user
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }


    public function flutterUserInfo(Request $request, $id)
    {
        try {
            $user = User::find($id);
    
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                ], 404);
            }
    
            // Return only the selected fields
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
            return response()->json([
                'status' => 'error',
                'message' => 'Server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}