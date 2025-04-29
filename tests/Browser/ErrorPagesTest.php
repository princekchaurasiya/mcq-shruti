<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ErrorPagesTest extends DuskTestCase
{
    /**
     * Test 404 page is displayed correctly
     */
    public function test_404_page_displays_correctly(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/non-existent-page-' . rand(1000, 9999))
                    ->screenshot('404-page');
        });
    }
} 