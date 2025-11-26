<?php
// app/Http/Middleware/CheckRole.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login')
                ->with('error', 'Please log in to access this page.');
        }

        $user = Auth::user();
        
        // Check if user has any of the required roles
        if (!in_array($user->role, $roles)) {
            // Log unauthorized access attempt for security
            Log::warning('Unauthorized role access attempt', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'required_roles' => $roles,
                'url' => $request->url(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()
            ]);

            // Handle JSON requests (API)
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Access denied. Insufficient permissions.',
                    'required_roles' => $roles,
                    'user_role' => $user->role
                ], 403);
            }

            // Redirect to appropriate dashboard based on user's actual role
            return $this->redirectToUserDashboard($user->role);
        }

        return $next($request);
    }

    /**
     * Redirect user to their appropriate dashboard
     *
     * @param string $role
     * @return \Illuminate\Http\RedirectResponse
     */
private function redirectToUserDashboard(string $role)
{
    $message = 'Access denied. You do not have permission to access this page.';
    
    // Prevent redirect loops
    if (request()->routeIs('student.dashboard', 'nurse.dashboard', 'dean.dashboard', 'dean.dashboard.bsit', 'dean.dashboard.bsba', 'dean.dashboard.educ', 'dashboard')) {
        abort(403, $message);
    }
    
    switch ($role) {
        case User::ROLE_STUDENT:
            return redirect()->route('student.dashboard')
                ->with('error', $message);
        case User::ROLE_NURSE:
            return redirect()->route('nurse.dashboard')
                ->with('error', $message);
        case User::ROLE_DEAN:
            // Check dean's email for department-specific redirect
            $email = strtolower(auth()->user()->email);
            
            if (str_contains($email, 'bsit')) {
                return redirect()->route('dean.dashboard.bsit')->with('error', $message);
            } elseif (str_contains($email, 'bsba')) {
                return redirect()->route('dean.dashboard.bsba')->with('error', $message);
            } elseif (str_contains($email, 'educ')) {
                return redirect()->route('dean.dashboard.educ')->with('error', $message);
            }
            
            return redirect()->route('dean.dashboard')->with('error', $message);
        default:
            return redirect()->route('home')
                ->with('error', 'Access denied. Invalid user role.');
    }
}

}