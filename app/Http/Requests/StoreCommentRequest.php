<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string|max:5000',
            'table' => 'required|string|in:products,shops',
            'commented_id' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Comment content is required.',
            'table.in' => 'Invalid comment type.',
            'commented_id.min' => 'Invalid target ID.',
        ];
    }
}
