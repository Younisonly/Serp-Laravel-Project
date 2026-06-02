<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizeInput
{
    protected $except = [
        'password',
        'password_confirmation',
        'current_password',
        'content',
        'description',
        'specifications',
        'highlights',
        'tags',
    ];

    public function handle(Request $request, Closure $next)
    {
        $inputs = $request->all();

        array_walk_recursive($inputs, function (&$value, $key) {
            if (is_string($value) && !in_array($key, $this->except, true)) {
                $value = strip_tags($value);
                $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
            }
        });

        $request->merge($inputs);

        return $next($request);
    }
}
