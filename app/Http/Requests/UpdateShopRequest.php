<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:120',
            'description' => 'nullable|string|max:2000',
            'iconname' => 'nullable|string|max:60',
            'open_at' => 'nullable|date_format:H:i',
            'close_at' => 'nullable|date_format:H:i',
            'days_open' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'contact_email' => 'nullable|email|max:100',
            'business_type' => 'nullable|string|max:60',
            'price_range' => 'nullable|string|max:10',
            'amenities' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:200',
            'instagram' => 'nullable|string|max:200',
            'X' => 'nullable|string|max:200',
            'whatsapp' => 'nullable|string|max:200',
            'facebook' => 'nullable|string|max:200',
            'phones' => 'nullable|array|max:5',
            'phones.*' => 'string|max:20',
            'logo' => 'nullable|image|max:5120',
            'backlogo' => 'nullable|image|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Shop name is required.',
            'name.max' => 'Shop name cannot exceed 120 characters.',
            'contact_email.email' => 'Please enter a valid email address.',
            'website.url' => 'Please enter a valid website URL.',
            'lat.between' => 'Latitude must be between -90 and 90.',
            'lng.between' => 'Longitude must be between -180 and 180.',
        ];
    }
}
