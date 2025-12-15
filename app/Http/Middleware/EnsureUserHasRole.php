<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user instanceof User) {
            throw new HttpException(401, 'Unauthorized');
        }

        if (!$user->hasAnyRole($roles)) {
            throw new HttpException(403, 'আপনার এই কাজের অনুমতি নেই।');
        }

        return $next($request);
    }
}
