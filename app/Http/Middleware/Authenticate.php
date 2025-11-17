<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     * Untuk API-only, kita tidak redirect. Laravel akan kembalikan JSON 401.
     */
    protected function redirectTo($request)
    {
        return null; // Jangan redirect, cukup kembalikan 401
    }
}
