<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class EnsureUserIsAuthorizedForNova
{
    public function handle($request, Closure $next)
    {
        if ($request->is('maintenance')) {
            return $next($request);
        }

        $user = Auth::guard(config('nova.guard'))->user();
        if (!$user || $user->email !== 'team@webmapp.it') {
            return redirect('/maintenance');
        }
        return $next($request);
    }
}
