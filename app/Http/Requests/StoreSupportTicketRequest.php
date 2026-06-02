<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:200',
            'content' => 'required|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Ticket title is required.',
            'content.required' => 'Ticket content is required.',
        ];
    }
}
