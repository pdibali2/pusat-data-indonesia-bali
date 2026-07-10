<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSession;

class IsLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika user belum login
        if (!Auth::check()) {
            return redirect('/login');
        }

        // Update last-seen for the current user session so we can detect
        // "stale" sessions (e.g. when user closed the tab and no longer
        // sends requests). Only update if a session id exists.
        try {
            $sessionId = $request->session()->getId();
            if ($sessionId) {
                UserSession::where('user_id', $request->user()->user_id)
                    ->where('session_token', $sessionId)
                    ->where('is_active', true)
                    ->update(['updated_at' => now()]);
            }
        } catch (\Throwable $e) {
            // Do not break the request if session update fails.
        }

        // Jika sudah login lanjutkan request
        return $next($request);
    }
}