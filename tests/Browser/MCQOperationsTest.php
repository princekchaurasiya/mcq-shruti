<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Str;

class MCQOperationsTest extends DuskTestCase
{
    /**
     * Test basic admin navigation
     */
    public function test_admin_basic_navigation(): void
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
                    ->screenshot('admin-operations-dashboard')
                    
                    // Visit MCQ tests page
                    ->visit('/teacher/mcq-tests')
                    ->assertPathIs('/teacher/mcq-tests')
                    ->screenshot('admin-mcq-tests-page');
        });
    }

    /**
     * Test basic student navigation
     */
    public function test_student_basic_navigation(): void
    {
        // Create a student user with random email
        $student = User::factory()->create([
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                    ->visit('/dashboard')
                    ->assertPathIs('/dashboard')
                    ->screenshot('student-operations-dashboard')
                    
                    // Try to visit available tests page
                    ->visit('/student/available-tests')
                    ->screenshot('student-available-tests');
        });
    }
} 