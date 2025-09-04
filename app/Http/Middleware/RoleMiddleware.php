<?php
// app/Http/Middleware/RoleMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Sizning hisobingiz bloklangan. Administrator bilan bog\'laning.'
            ]);
        }

        // Check if user has required role
        if (!empty($roles) && !in_array($user->role, $roles)) {
            // Redirect based on user's actual role
            switch ($user->role) {
                case 'admin':
                    return redirect('/dashboard')->withErrors([
                        'access' => 'Sizda bu sahifaga kirish huquqi yo\'q.'
                    ]);
                case 'manager':
                    return redirect('/contracts')->withErrors([
                        'access' => 'Sizda bu sahifaga kirish huquqi yo\'q.'
                    ]);
                case 'employee':
                    return redirect('/contracts')->withErrors([
                        'access' => 'Sizda bu sahifaga kirish huquqi yo\'q.'
                    ]);
                default:
                    return redirect('/login');
            }
        }

        return $next($request);
    }
}

// Register this middleware in app/Http/Kernel.php:
/*
protected $routeMiddleware = [
    // ... existing middleware
    'role' => \App\Http\Middleware\RoleMiddleware::class,
];
*/
