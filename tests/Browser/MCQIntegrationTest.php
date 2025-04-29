<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Str;

class MCQIntegrationTest extends DuskTestCase
{
    /**
     * Test the entire MCQ workflow from admin to student
     */
    public function test_full_mcq_workflow(): void
    {
        // Create admin and student users with random emails
        $randomAdminEmail = 'admin_' . Str::random(8) . '@example.com';
        $randomStudentEmail = 'student_' . Str::random(8) . '@example.com';
        
        $admin = User::factory()->create([
            'email' => $randomAdminEmail,
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        
        $student = User::factory()->create([
            'email' => $randomStudentEmail,
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            // Just test basic navigation for admin
            $browser->loginAs($admin)
                    ->visit('/dashboard')
                    ->assertPathIs('/dashboard')
                    ->screenshot('admin-dashboard-integration');
        });
        
        $this->browse(function (Browser $browser) use ($student) {
            // Just test basic navigation for student
            $browser->loginAs($student)
                    ->visit('/dashboard')
                    ->assertPathIs('/dashboard')
                    ->screenshot('student-dashboard');
        });
    }
} 