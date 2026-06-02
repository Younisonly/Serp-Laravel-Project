<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'table' => 'required|string|in:products,shops,comments',
            'reported_id' => 'required|integer|min:1',
            'content' => 'required|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'table.in' => 'Invalid report type.',
            'content.required' => 'Report reason is required.',
        ];
    }
}
