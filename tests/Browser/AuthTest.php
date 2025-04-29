<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AuthTest extends DuskTestCase
{
    /**
     * Test user can access dashboard after login
     */
    public function test_user_can_access_dashboard(): void
    {
        // Create a user
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/dashboard')
                    ->assertPathIs('/dashboard')
                    // Take screenshot of dashboard
                    ->screenshot('user-dashboard');
        });
    }
} 