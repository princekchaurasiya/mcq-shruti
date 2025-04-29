<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProfileTest extends DuskTestCase
{
    /**
     * Test profile page loads correctly
     */
    public function test_profile_page_loads(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/profile')
                    ->assertPathIs('/profile')
                    ->screenshot('profile-page');
        });
    }
} 