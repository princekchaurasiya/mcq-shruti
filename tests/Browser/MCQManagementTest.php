<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Str;

class MCQManagementTest extends DuskTestCase
{
    /**
     * Test MCQ test management as admin
     */
    public function test_admin_can_manage_tests(): void
    {
        // Create an admin user with random email
        $admin = User::factory()->create([
            'email' => 'admin_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/dashboard')
                    ->assertPathIs('/dashboard')
                    ->screenshot('admin-mcq-dashboard')
                    
                    // Verify we can visit the test creation page
                    ->visit('/teacher/mcq-tests/create')
                    ->assertPathIs('/teacher/mcq-tests/create')
                    ->screenshot('admin-mcq-create');
        });
    }

    /**
     * Test MCQ test management as teacher
     */
    public function test_teacher_can_manage_tests(): void
    {
        // Create a teacher user with random email
        $teacher = User::factory()->create([
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        $this->browse(function (Browser $browser) use ($teacher) {
            $browser->loginAs($teacher)
                    ->visit('/dashboard')
                    ->assertPathIs('/dashboard')
                    
                    // Verify we can visit the test creation page
                    ->visit('/teacher/mcq-tests/create')
                    ->assertPathIs('/teacher/mcq-tests/create')
                    ->screenshot('teacher-mcq-create');
        });
    }
} 