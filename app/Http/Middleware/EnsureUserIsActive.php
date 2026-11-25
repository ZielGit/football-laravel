<?php

namespace App\Http\Middleware;

use App\Exceptions\AccountDisabledException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->is_active === false) {
            throw new AccountDisabledException;
        }

        return $next($request);
    }
}
