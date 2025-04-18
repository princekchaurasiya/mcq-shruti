<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Create custom log channels for different user types
        $this->configureLogging();
    }

    protected function configureLogging()
    {
        // Create separate log files for different user types
        $userTypes = ['student', 'teacher', 'admin', 'auth', 'system'];

        foreach ($userTypes as $type) {
            Log::build([
                'driver' => 'single',
                'path' => storage_path("logs/{$type}.log"),
            ]);
        }
    }
}
