<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Route;

class Authenticate extends Middleware
{
    protected function redirectTo($request): ?string
    {
        // Exclude Filament admin routes - let Filament handle its own redirects
        if ($request->is('admin/*')) {
            return '/admin/login';
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        if (Route::has('login')) {
            return route('login');
        }

        return null;
    }
}
