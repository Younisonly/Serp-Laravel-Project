<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20|regex:/^[0-9+\-\(\)\s]+$/',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'gender' => 'nullable|string|in:male,female,other',
            'birth_date' => 'nullable|date|before:today',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'old_password' => 'required_with:password|string|min:1',
            'password' => 'nullable|string|min:8|max:128|confirmed',
            'password_confirmation' => 'required_with:password|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'birth_date.before' => 'Birth date must be in the past.',
            'image.max' => 'Image must not exceed 2MB.',
            'password.min' => 'Password must be at least 8 characters.',
            'old_password.required_with' => 'Current password is required to set a new password.',
        ];
    }
}
