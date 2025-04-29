<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Error;
use ErrorException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
        
        // Handle model not found exceptions
        $this->renderable(function (ModelNotFoundException $e, $request) {
            $modelName = basename(str_replace('\\', '/', $e->getModel()));
            
            // Log the error
            Log::error("Model not found: {$modelName}", [
                'exception' => $e,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => $request->user() ? $request->user()->id : 'guest'
            ]);
            
            // For API requests return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => "The requested {$modelName} was not found."
                ], 404);
            }
            
            // For web requests, render a custom error page
            if (View::exists('errors.custom')) {
                return response()->view('errors.custom', [
                    'title' => 'Resource Not Found', 
                    'message' => "Sorry, the {$modelName} you are looking for could not be found.",
                    'details' => "This could be because the resource has been removed or the URL is incorrect."
                ], 404);
            }
            
            return null; // Let Laravel handle it if our view doesn't exist
        });
        
        // Handle 404 errors
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Resource not found'], 404);
            }
            
            if (View::exists('errors.custom')) {
                return response()->view('errors.custom', [
                    'title' => 'Page Not Found',
                    'message' => 'Sorry, the page you are looking for could not be found.',
                    'details' => 'Please check the URL and try again.'
                ], 404);
            }
            
            return null;
        });
        
        // Handle database query exceptions
        $this->renderable(function (QueryException $e, $request) {
            Log::error('Database error occurred', [
                'exception' => $e,
                'sql' => $e->getSql() ?? 'Unknown',
                'bindings' => $e->getBindings() ?? [],
                'url' => $request->fullUrl(),
                'user_id' => $request->user() ? $request->user()->id : 'guest'
            ]);
            
            if (View::exists('errors.custom')) {
                return response()->view('errors.custom', [
                    'title' => 'Database Error',
                    'message' => 'A database error occurred while processing your request.',
                    'details' => app()->environment('production') ? 
                        'Please try again later or contact support if the issue persists.' : 
                        $e->getMessage()
                ], 500);
            }
            
            return null;
        });
        
        // Handle general exceptions
        $this->renderable(function (Throwable $e, $request) {
            // Don't handle validation exceptions - let the framework handle them
            if ($e instanceof ValidationException || $e instanceof AuthenticationException) {
                return null;
            }
            
            // Skip if we're already handled more specific exceptions
            if ($e instanceof ModelNotFoundException || 
                $e instanceof NotFoundHttpException || 
                $e instanceof QueryException) {
                return null;
            }
            
            Log::error('Unhandled exception occurred', [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'url' => $request->fullUrl(),
                'user_id' => $request->user() ? $request->user()->id : 'guest'
            ]);
            
            if (View::exists('errors.custom')) {
                return response()->view('errors.custom', [
                    'title' => 'Error Occurred',
                    'message' => 'Sorry, an error occurred while processing your request.',
                    'details' => app()->environment('production') ? 
                        'Please try again later or contact support if the issue persists.' : 
                        $e->getMessage()
                ], 500);
            }
            
            return null;
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        // Log the exception
        Log::error('Exception: ' . get_class($exception) . ' - ' . $exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Handle Class not found errors (common in Laravel when models don't exist)
        if ($exception instanceof Error && str_contains($exception->getMessage(), 'not found')) {
            $message = $exception->getMessage();
            $classname = $this->extractClassNameFromError($message);
            
            return $this->renderCustomError(
                $request,
                'System Error', 
                "The requested resource type is not available: {$classname}", 
                "This feature might be temporarily unavailable or there may be a configuration issue. Please try again later or contact support.",
                500
            );
        }

        // Custom handling for specific exception types
        if ($exception instanceof ModelNotFoundException) {
            return $this->renderCustomError($request, 'Resource Not Found', 'The requested resource could not be found.', 'Please check the URL and try again.', 404);
        }

        if ($exception instanceof NotFoundHttpException) {
            return $this->renderCustomError($request, 'Page Not Found', 'The requested page does not exist.', 'Please check the URL and try again.', 404);
        }

        if ($exception instanceof QueryException) {
            return $this->renderCustomError($request, 'Database Error', 'There was an error processing your request.', 'Please try again later.', 500);
        }

        // Handle all other exceptions with our custom view
        $statusCode = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
        $message = $exception->getMessage() ?: 'An unexpected error has occurred';
        
        return $this->renderCustomError(
            $request,
            'Application Error',
            $message,
            'Please try again or contact support if the issue persists.',
            $statusCode
        );
    }

    /**
     * Render a custom error response
     *
     * @param \Illuminate\Http\Request $request
     * @param string $title
     * @param string $message
     * @param string $details
     * @param int $statusCode
     * @return \Illuminate\Http\Response
     */
    protected function renderCustomError($request, $title, $message, $details = null, $statusCode = 500)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => $title,
                'message' => $message,
                'details' => $details,
            ], $statusCode);
        }

        return response()->view('errors.custom', [
            'title' => $title,
            'message' => $message,
            'details' => $details,
        ], $statusCode);
    }
    
    /**
     * Extract class name from error message
     * 
     * @param string $message
     * @return string
     */
    private function extractClassNameFromError($message)
    {
        // Try to extract class name from common error formats
        if (preg_match('/Class "(.*?)" not found/', $message, $matches)) {
            return $matches[1];
        }
        
        if (preg_match('/Class (.*?) not found/', $message, $matches)) {
            return $matches[1];
        }
        
        return "Unknown";
    }
} 