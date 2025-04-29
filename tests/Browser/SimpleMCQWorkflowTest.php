<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Subject;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SimpleMCQWorkflowTest extends DuskTestCase
{
    /**
     * A basic test to verify core functionality.
     */
    public function test_basic_mcq_workflow(): void
    {
        // Create a user
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            // Test basic authenticated navigation
            $browser->loginAs($user)
                    ->visit('/dashboard')
                    ->assertPathIs('/dashboard')
                    ->screenshot('dashboard')
                    
                    // Test navigation to profile
                    ->visit('/profile')
                    ->assertPathIs('/profile')
                    ->screenshot('profile')
                    
                    // Return to dashboard
                    ->visit('/dashboard')
                    ->assertPathIs('/dashboard')
                    ->screenshot('dashboard-return');
        });
    }

    /**
     * Test to check if we can view available tests
     */
    public function test_can_view_available_tests(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/dashboard')
                    ->assertPathIs('/dashboard')
                    ->screenshot('dashboard-before-tests')
                    
                    // Attempt to view available tests
                    // Note: This path needs to be adjusted based on your actual navigation structure
                    ->visit('/mcq/list') // Or whatever path shows tests
                    ->screenshot('mcq-list');
        });
    }

    /**
     * Test teacher can access test creation page
     */
    public function test_teacher_can_access_test_creation(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'role' => 'teacher',
        ]);

        $this->browse(function (Browser $browser) use ($teacher) {
            $browser->loginAs($teacher)
                    ->visit('/dashboard')
                    ->assertPathIs('/dashboard')
                    ->screenshot('teacher-dashboard')
                    
                    // Try to navigate to test creation page
                    ->visit('/teacher/mcq-tests/create')
                    ->screenshot('teacher-test-creation')
                    // Just check that the page loads without errors
                    ->assertDontSee('Sorry, the page you are looking for could not be found.');
        });
    }

    /**
     * Test admin can also access test creation page
     */
    public function test_admin_can_access_test_creation(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/dashboard')
                    ->assertPathIs('/dashboard')
                    ->screenshot('admin-dashboard')
                    
                    // Check if admin can access the test creation page
                    ->visit('/teacher/mcq-tests/create')
                    ->screenshot('admin-test-creation')
                    // Just check that the page loads without errors
                    ->assertDontSee('Sorry, the page you are looking for could not be found.');
        });
    }

    /**
     * Test teacher can access test management page
     */
    public function test_teacher_can_manage_tests(): void
    {
        // Create a teacher user
        $teacher = User::factory()->create([
            'role' => 'teacher',
        ]);

        $this->browse(function (Browser $browser) use ($teacher) {
            $browser->loginAs($teacher)
                    ->visit('/dashboard')
                    ->assertPathIs('/dashboard')
                    ->screenshot('teacher-dashboard-manage')
                    
                    // Navigate to tests listing page
                    ->visit('/teacher/mcq-tests')
                    ->screenshot('teacher-tests-listing')
                    ->assertDontSee('Sorry, the page you are looking for could not be found.');
        });
    }

    /**
     * Test admin can access test management page
     */
    public function test_admin_can_manage_tests(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/dashboard')
                    ->assertPathIs('/dashboard')
                    ->screenshot('admin-dashboard-manage')
                    
                    // Navigate to tests listing page
                    ->visit('/teacher/mcq-tests')
                    ->screenshot('admin-tests-listing')
                    ->assertDontSee('Sorry, the page you are looking for could not be found.');
        });
    }
} 