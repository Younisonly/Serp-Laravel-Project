<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ImageAll implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!$value->isValid()) {
            return false;
        }

        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/webp',
            'image/heic',
            'image/heif',
        ];

        return in_array($value->getMimeType(), $allowedMimes);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid image file (JPEG, PNG, GIF, BMP, WebP, HEIC, HEIF).';
    }
}
