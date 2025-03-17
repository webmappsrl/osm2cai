<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class MigrationNoticeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip middleware if user has already seen the notice
        if (Session::has('migration_notice_seen') || $request->is('login*') || $request->is('nova/login*')) {
            return $next($request);
        }

        // Store current route in session to return after viewing notice
        Session::put('previous_route', $request->fullUrl());

        Session::save();

        return redirect()->route('migration.notice');
    }
}
