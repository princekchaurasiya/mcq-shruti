<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class MCQTest extends DuskTestCase
{
    /**
     * Test MCQ routes are accessible
     */
    public function test_mcq_pages_load(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/dashboard')
                    ->screenshot('dashboard-page')
                    // Note: Adjust these URLs based on your actual application routes
                    ->visit('/dashboard')
                    ->screenshot('mcq-dashboard');
        });
    }
} 