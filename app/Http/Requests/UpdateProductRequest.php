<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:200', Rule::unique('products', 'name')->where('shop_id', $this->route('shopId'))->ignore($this->route('productId'))],
            'description' => 'nullable|string|max:5000',
            'price' => 'sometimes|required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'discount_start' => 'nullable|date',
            'discount_end' => 'nullable|date|after_or_equal:discount_start',
            'brand' => 'nullable|string|max:100',
            'model_number' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'size' => 'nullable|string|max:50',
            'material' => 'nullable|string|max:100',
            'weight' => 'nullable|numeric|min:0',
            'barcode' => 'nullable|string|max:50',
            'warranty' => 'nullable|boolean',
            'warranty_start' => 'nullable|date',
            'warranty_end' => 'nullable|date|after_or_equal:warranty_start',
            'warranty_description' => 'nullable|string|max:1000',
            'highlights' => 'nullable|array',
            'highlights.*' => 'string|max:200',
            'specifications' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'shipping_info' => 'nullable|string|max:2000',
            'return_policy' => 'nullable|string|max:2000',
            'delivery_info' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:2000',
            'type_id' => 'nullable|exists:product_categories,id',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|max:5120',
        ];
    }
}
