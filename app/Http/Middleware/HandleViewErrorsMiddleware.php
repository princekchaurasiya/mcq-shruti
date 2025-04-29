<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class HandleViewErrorsMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Process the request
        try {
            $response = $next($request);
            return $response;
        } catch (\ErrorException $e) {
            // Handle undefined variable errors and other view errors
            if (strpos($e->getMessage(), 'Undefined variable') !== false) {
                Log::error('View error: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'url' => $request->fullUrl()
                ]);

                // If it's an AJAX request, return JSON error
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'An error occurred while processing your request.',
                        'message' => 'The application encountered an internal error. Please try again later.'
                    ], 500);
                }

                // For regular requests, redirect to a custom error page
                return response()->view('errors.custom', [
                    'errorTitle' => 'Data Processing Error',
                    'errorMessage' => 'We encountered an issue while processing the data for this page. Our team has been notified.'
                ], 500);
            }

            // Re-throw other exceptions
            throw $e;
        }
    }
} 