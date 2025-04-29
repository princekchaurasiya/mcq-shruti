<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class NavigationTest extends DuskTestCase
{
    /**
     * Test main pages load correctly
     */
    public function test_main_pages_load(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->screenshot('home-page')
                    ->assertPathIs('/')
                    ->visit('/login')
                    ->screenshot('login-page')
                    ->assertPathIs('/login')
                    ->visit('/register')
                    ->screenshot('register-page')
                    ->assertPathIs('/register');
        });
    }
} 