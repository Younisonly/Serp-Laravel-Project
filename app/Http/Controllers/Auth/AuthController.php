<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\FileUploadHelper;
use App\Helpers\HashHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        
        try{
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:15',
            'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

      

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => HashHelper::make($request->password),
            'phone' => $request->phone,
            // 'image' => $imagePath,
            'status' => 'active',
            'type'=>"user"
        ]);

        $imageUrl = FileUploadHelper::upload($request, 'image', 'user', $user->id, 'users');

        $user->update([
            'image' => $imageUrl,
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'تم التسجيل بنجاح',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'image' => $imageUrl ?? null,
                'status' => $user->status,
                'type'=>$user->type
            ],
            'token' => $token,
        ], 201);
    }catch (\Throwable $e) {
       
        // Return clear error message to API consumer
        return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong while insert user.',
            'exception' => get_class($e),
            'error_message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            // 'trace' => $e->getTraceAsString(), // Uncomment only in development
        ], 500);
    }
}
   
    public function login(Request $request)
    {
        try{
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'error' => 'البريد أو كلمة المرور غير صحيحة',
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'status' => 'error',
                'error' => 'الحساب غير مفعل',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'تم تسجيل الدخول',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'image' => $user->image ,
                'status' => $user->status,
                'type'=>$user->type

            ],
            'token' => $token,
        ]);
    }
    catch (\Throwable $e)
     {
       
        // Return clear error message to API consumer
        return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong while fetching nearby shops.',
            'exception' => get_class($e),
            'error_message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            // 'trace' => $e->getTraceAsString(), // Uncomment only in development
        ], 500);
    }}

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        try {
            // Deletes the token currently used by the user (so he’s logged out from this device only)
            $request->user()->currentAccessToken()->delete();
    
            return response()->json([
                'status' => 'success',
                'message' => 'تم تسجيل الخروج بنجاح',
            ], 200);
    
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء تسجيل الخروج',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
       
    
    public function me(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'user' => $request->user() // Sanctum returns the authenticated user
        ], 200);
    }

       ///////////
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            abort(response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك، يجب تسجيل الدخول أولاً'
            ], 401));
        }
    }
    

}