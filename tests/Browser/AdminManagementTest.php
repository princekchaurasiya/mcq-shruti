<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Test;
use App\Models\Question;
use App\Models\TestAttempt;
use App\Models\Setting;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Str;

class AdminManagementTest extends DuskTestCase
{
    /**
     * Test admin can view user management page
     */
    public function test_admin_can_view_user_management(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'email' => 'admin_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Create some regular users
        $teacher1 = User::factory()->create([
            'name' => 'Teacher One',
            'email' => 'teacher1_' . Str::random(8) . '@example.com',
            'role' => 'teacher',
        ]);

        $teacher2 = User::factory()->create([
            'name' => 'Teacher Two',
            'email' => 'teacher2_' . Str::random(8) . '@example.com',
            'role' => 'teacher',
        ]);

        $student1 = User::factory()->create([
            'name' => 'Student One',
            'email' => 'student1_' . Str::random(8) . '@example.com',
            'role' => 'student',
        ]);

        $student2 = User::factory()->create([
            'name' => 'Student Two',
            'email' => 'student2_' . Str::random(8) . '@example.com',
            'role' => 'student',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $teacher1, $student1) {
            $browser->loginAs($admin)
                    ->visit('/admin/dashboard')
                    ->assertSee('Admin Dashboard')
                    ->screenshot('admin-dashboard')
                    
                    // View user management page
                    ->click('@manage-users')
                    ->assertPathIs('/admin/users')
                    ->assertSee('User Management')
                    ->assertSee($teacher1->name)
                    ->assertSee($student1->name)
                    ->screenshot('user-management');
            
            // Test filtering users by role
            $browser->select('role_filter', 'teacher')
                    ->press('Filter')
                    ->assertSee($teacher1->name)
                    ->assertDontSee($student1->name)
                    ->screenshot('filtered-users-teachers');
            
            $browser->select('role_filter', 'student')
                    ->press('Filter')
                    ->assertSee($student1->name)
                    ->assertDontSee($teacher1->name)
                    ->screenshot('filtered-users-students');
        });
    }

    /**
     * Test admin can create a new user
     */
    public function test_admin_can_create_user(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'email' => 'admin_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $newUserEmail = 'new_teacher_' . Str::random(8) . '@example.com';

        $this->browse(function (Browser $browser) use ($admin, $newUserEmail) {
            $browser->loginAs($admin)
                    ->visit('/admin/users')
                    ->click('@add-user')
                    ->assertSee('Create New User')
                    ->screenshot('add-user-form')
                    
                    // Fill in user details
                    ->type('name', 'New Teacher User')
                    ->type('email', $newUserEmail)
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    ->select('role', 'teacher')
                    ->press('Create User')
                    ->assertPathIs('/admin/users')
                    ->assertSee('User created successfully')
                    ->assertSee('New Teacher User')
                    ->screenshot('after-user-creation');
            
            // Verify user exists in database
            $this->assertDatabaseHas('users', [
                'email' => $newUserEmail,
                'role' => 'teacher'
            ]);
        });
    }

    /**
     * Test admin can edit a user
     */
    public function test_admin_can_edit_user(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'email' => 'admin_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Create a user to edit
        $user = User::factory()->create([
            'name' => 'User To Edit',
            'email' => 'edit_user_' . Str::random(8) . '@example.com',
            'role' => 'student',
        ]);

        $updatedEmail = 'updated_' . Str::random(8) . '@example.com';

        $this->browse(function (Browser $browser) use ($admin, $user, $updatedEmail) {
            $browser->loginAs($admin)
                    ->visit('/admin/users')
                    ->click('@edit-user-' . $user->id)
                    ->assertSee('Edit User')
                    ->assertInputValue('name', 'User To Edit')
                    ->screenshot('edit-user-form')
                    
                    // Update user details
                    ->type('name', 'Updated User Name')
                    ->type('email', $updatedEmail)
                    ->select('role', 'teacher')
                    ->press('Update User')
                    ->assertPathIs('/admin/users')
                    ->assertSee('User updated successfully')
                    ->assertSee('Updated User Name')
                    ->screenshot('after-user-update');
            
            // Verify user details updated in database
            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'name' => 'Updated User Name',
                'email' => $updatedEmail,
                'role' => 'teacher'
            ]);
        });
    }

    /**
     * Test admin can delete a user
     */
    public function test_admin_can_delete_user(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'email' => 'admin_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Create a user to delete
        $user = User::factory()->create([
            'name' => 'User To Delete',
            'email' => 'delete_user_' . Str::random(8) . '@example.com',
            'role' => 'student',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $user) {
            $browser->loginAs($admin)
                    ->visit('/admin/users')
                    ->assertSee('User To Delete')
                    ->screenshot('before-delete-user')
                    
                    // Delete the user
                    ->click('@delete-user-' . $user->id)
                    ->acceptDialog() // Confirm deletion
                    ->assertPathIs('/admin/users')
                    ->assertSee('User deleted successfully')
                    ->assertDontSee('User To Delete')
                    ->screenshot('after-delete-user');
            
            // Verify user deleted from database
            $this->assertDatabaseMissing('users', [
                'id' => $user->id,
                'deleted_at' => null
            ]);
        });
    }

    /**
     * Test admin can manage system settings
     */
    public function test_admin_can_manage_system_settings(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'email' => 'admin_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Create some system settings
        Setting::updateOrCreate(
            ['key' => 'site_name'],
            ['value' => 'MCQ Testing Platform']
        );

        Setting::updateOrCreate(
            ['key' => 'allow_registration'],
            ['value' => 'true']
        );

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin/dashboard')
                    ->click('@system-settings')
                    ->assertPathIs('/admin/settings')
                    ->assertSee('System Settings')
                    ->assertInputValue('site_name', 'MCQ Testing Platform')
                    ->screenshot('system-settings')
                    
                    // Update settings
                    ->type('site_name', 'Updated MCQ Platform')
                    ->select('allow_registration', 'false')
                    ->press('Save Settings')
                    ->assertPathIs('/admin/settings')
                    ->assertSee('Settings updated successfully')
                    ->screenshot('after-settings-update');
            
            // Verify settings updated in database
            $this->assertEquals('Updated MCQ Platform', Setting::where('key', 'site_name')->first()->value);
            $this->assertEquals('false', Setting::where('key', 'allow_registration')->first()->value);
        });
    }

    /**
     * Test admin can view test statistics
     */
    public function test_admin_can_view_test_statistics(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'email' => 'admin_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Create a teacher
        $teacher = User::factory()->create([
            'role' => 'teacher',
        ]);

        // Create students
        $students = User::factory()->count(5)->create([
            'role' => 'student',
        ]);

        // Create tests
        $test1 = Test::factory()->create([
            'title' => 'Statistics Test 1',
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        $test2 = Test::factory()->create([
            'title' => 'Statistics Test 2',
            'user_id' => $teacher->id,
            'status' => 'published',
        ]);

        // Create questions
        $questions = Question::factory()->count(10)->create([
            'test_id' => $test1->id,
        ]);

        // Create test attempts with various scores
        foreach ($students as $student) {
            TestAttempt::factory()->create([
                'user_id' => $student->id,
                'test_id' => $test1->id,
                'score' => rand(60, 100),
                'started_at' => now()->subDays(rand(1, 7)),
                'completed_at' => now()->subDays(rand(1, 7))->addMinutes(rand(10, 30)),
            ]);
        }

        $this->browse(function (Browser $browser) use ($admin, $test1) {
            $browser->loginAs($admin)
                    ->visit('/admin/dashboard')
                    ->click('@view-statistics')
                    ->assertPathIs('/admin/statistics')
                    ->assertSee('Test Statistics')
                    ->assertSee('Total Tests')
                    ->assertSee('Total Questions')
                    ->assertSee('Total Test Attempts')
                    ->screenshot('statistics-overview')
                    
                    // View detailed test statistics
                    ->click('@test-stats-' . $test1->id)
                    ->assertPathIs('/admin/tests/' . $test1->id . '/statistics')
                    ->assertSee('Statistics for: Statistics Test 1')
                    ->assertSee('Average Score')
                    ->assertSee('Highest Score')
                    ->assertSee('Lowest Score')
                    ->assertSee('Total Attempts')
                    ->screenshot('detailed-test-statistics');
        });
    }

    /**
     * Test admin can backup and restore database
     */
    public function test_admin_can_backup_database(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'email' => 'admin_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin/dashboard')
                    ->click('@database-tools')
                    ->assertPathIs('/admin/database')
                    ->assertSee('Database Management')
                    ->assertSee('Backup Database')
                    ->assertSee('Restore Database')
                    ->screenshot('database-management')
                    
                    // Initiate database backup
                    ->click('@backup-database')
                    ->waitForText('Database backup created successfully')
                    ->assertSee('Database backup created successfully')
                    ->screenshot('after-database-backup');
            
            // The actual file creation would need to be mocked in a real test
            // Here we just verify the UI workflow
        });
    }

    /**
     * Test admin can manage user permissions
     */
    public function test_admin_can_manage_permissions(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'email' => 'admin_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Create a teacher to manage permissions
        $teacher = User::factory()->create([
            'name' => 'Permission Teacher',
            'email' => 'perm_teacher_' . Str::random(8) . '@example.com',
            'role' => 'teacher',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $teacher) {
            $browser->loginAs($admin)
                    ->visit('/admin/users')
                    ->click('@manage-permissions-' . $teacher->id)
                    ->assertPathIs('/admin/users/' . $teacher->id . '/permissions')
                    ->assertSee('Manage Permissions')
                    ->assertSee('Permission Teacher')
                    ->screenshot('manage-permissions')
                    
                    // Change permissions
                    ->check('permissions[create_tests]')
                    ->check('permissions[manage_own_tests]')
                    ->uncheck('permissions[manage_all_tests]')
                    ->check('permissions[view_statistics]')
                    ->press('Update Permissions')
                    ->assertPathIs('/admin/users')
                    ->assertSee('Permissions updated successfully')
                    ->screenshot('after-permissions-update');
            
            // Verify permissions in database
            // This would typically check a permissions table or user attributes
            // The implementation depends on how permissions are stored
        });
    }
} 