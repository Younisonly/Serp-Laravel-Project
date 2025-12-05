<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Hash;

class HashHelper
{
    /**
     * Hash a password the same way Laravel Auth does.
     */
    public static function make(string $plain): string
    {
        // identical to Hash::make() → bcrypt / cost 10
        return Hash::make($plain);
    }
}