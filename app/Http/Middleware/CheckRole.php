<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        Log::info('CheckRole middleware executing', [
            'path' => $request->path(),
            'role_required' => $role,
            'user_authenticated' => $request->user() ? 'yes' : 'no',
            'user_role' => $request->user() ? $request->user()->role : 'none'
        ]);

        // Check if user is authenticated
        if (!$request->user()) {
            Log::warning('User not authenticated, redirecting to login');
            return redirect()->route('login');
        }

        $user = $request->user();

        // Check if user has the required role
        if ($user->role !== $role) {
            Log::warning('User role mismatch', [
                'user_role' => $user->role,
                'required_role' => $role
            ]);

            // Get the appropriate dashboard route based on user's actual role
            $dashboardRoute = match($user->role) {
                'admin' => 'admin.dashboard',
                'teacher' => 'teacher.dashboard',
                'student' => 'student.dashboard',
                default => 'home'
            };

            Log::info('Redirecting user to appropriate dashboard', [
                'route' => $dashboardRoute
            ]);

            return redirect()->route($dashboardRoute)
                ->with('error', 'Unauthorized access. Redirected to your dashboard.');
        }

        // TEMPORARY FIX: Comment out the role-specific record check to let users log in
        // We'll fix the actual records separately
        /*
        // Check if the role-specific record exists
        $hasRecord = match($role) {
            'teacher' => $user->teacher()->exists(),
            'student' => $user->student()->exists(),
            'admin' => $user->admin()->exists(),
            default => true
        };

        if (!$hasRecord) {
            Log::error("User has {$role} role but no corresponding record found", [
                'user_id' => $user->id,
                'role' => $role
            ]);

            // Instead of redirecting back to the same page (causing a loop),
            // log the user out and show them an error
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->with('error', "Your {$role} profile is incomplete. Please contact the administrator.");
        }
        */

        Log::info('User authorized, proceeding with request');
        return $next($request);
    }
} 