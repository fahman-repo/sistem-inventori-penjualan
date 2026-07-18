<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleDarkMode
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if dark mode is active via session
        $darkMode = session('adminlte_dark_mode');

        if ($darkMode === null) {
            // Fallback to config default
            $darkMode = config('adminlte.layout_dark_mode', false);
        }

        if ($darkMode) {
            // Override navbar classes to use dark variant
            config([
                'adminlte.classes_topnav' => 'navbar-dark',
            ]);
        }

        return $next($request);
    }
}