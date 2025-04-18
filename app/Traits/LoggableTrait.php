<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

trait LoggableTrait
{
    protected function logInfo($message, $context = [])
    {
        $this->log('info', $message, $context);
    }

    protected function logWarning($message, $context = [])
    {
        $this->log('warning', $message, $context);
    }

    protected function logError($message, $context = [])
    {
        $this->log('error', $message, $context);
    }

    protected function log($level, $message, $context = [])
    {
        try {
            $user = Auth::user();
            $userType = 'system';
            $userId = null;

            if ($user) {
                if ($user->teacher) {
                    $userType = 'teacher';
                    $userId = $user->teacher->id;
                } elseif ($user->student) {
                    $userType = 'student';
                    $userId = $user->student->id;
                } elseif ($user->admin) {
                    $userType = 'admin';
                    $userId = $user->admin->id;
                }
            }

            $context = array_merge($context, [
                'user_type' => $userType,
                'user_id' => $userId,
                'ip' => request()->ip(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'timestamp' => now()->toDateTimeString()
            ]);

            // Try to log to user-specific channel, fallback to single channel if it fails
            try {
                Log::channel($userType)->$level($message, $context);
            } catch (\Exception $e) {
                Log::channel('single')->$level($message, array_merge($context, [
                    'original_channel' => $userType,
                    'channel_error' => $e->getMessage()
                ]));
            }
        } catch (\Exception $e) {
            // If all else fails, log to the default channel
            Log::error('Logging failed', [
                'intended_message' => $message,
                'intended_context' => $context,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function logAuthEvent($message, $context = [])
    {
        try {
            $baseContext = [
                'ip' => request()->ip(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'timestamp' => now()->toDateTimeString()
            ];

            try {
                Log::channel('auth')->info($message, array_merge($context, $baseContext));
            } catch (\Exception $e) {
                Log::channel('single')->info($message, array_merge($context, $baseContext, [
                    'original_channel' => 'auth',
                    'channel_error' => $e->getMessage()
                ]));
            }
        } catch (\Exception $e) {
            Log::error('Auth logging failed', [
                'intended_message' => $message,
                'intended_context' => $context,
                'error' => $e->getMessage()
            ]);
        }
    }
} 