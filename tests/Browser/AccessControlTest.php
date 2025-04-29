<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Str;

class AccessControlTest extends DuskTestCase
{
    /**
     * Test admin access control
     */
    public function test_admin_access_control(): void
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
                    ->screenshot('admin-dashboard')
                    
                    // Admin should access teacher test page
                    ->visit('/teacher/mcq-tests')
                    ->assertPathIs('/teacher/mcq-tests')
                    ->screenshot('admin-mcq-tests')
                    
                    // Admin should access user management
                    ->visit('/admin/users')
                    ->assertPathIs('/admin/users')
                    ->screenshot('admin-user-management');
        });
    }

    /**
     * Test teacher access control
     */
    public function test_teacher_access_control(): void
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
                    ->screenshot('teacher-dashboard')
                    
                    // Teacher should access test creation
                    ->visit('/teacher/mcq-tests')
                    ->assertPathIs('/teacher/mcq-tests')
                    ->screenshot('teacher-mcq-tests');
        });
    }

    /**
     * Test student access control
     */
    public function test_student_access_control(): void
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
                    ->screenshot('student-dashboard')
                    
                    // Student should access available tests
                    ->visit('/student/available-tests')
                    ->assertPathIs('/student/available-tests')
                    ->screenshot('student-available-tests');
        });
    }
} 