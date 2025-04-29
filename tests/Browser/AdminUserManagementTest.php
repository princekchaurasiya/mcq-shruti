<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Str;

class AdminUserManagementTest extends DuskTestCase
{
    /**
     * Test admin can view all users
     */
    public function test_admin_can_view_all_users(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'email' => 'admin_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Create a teacher
        $teacher = User::factory()->create([
            'name' => 'Test Teacher',
            'email' => 'teacher_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        // Create a student
        $student = User::factory()->create([
            'name' => 'Test Student',
            'email' => 'student_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $teacher, $student) {
            $browser->loginAs($admin)
                    ->visit('/dashboard')
                    ->screenshot('admin-dashboard')
                    ->visit('/admin/users')
                    ->assertSee('User Management')
                    ->assertSee($teacher->name)
                    ->assertSee($teacher->email)
                    ->assertSee('Teacher')
                    ->assertSee($student->name)
                    ->assertSee($student->email)
                    ->assertSee('Student')
                    ->screenshot('admin-user-list');
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
                    ->screenshot('before-create-user')
                    ->click('@create-user-button')
                    ->assertSee('Create New User')
                    ->screenshot('create-user-form')
                    ->type('name', 'New Teacher User')
                    ->type('email', $newUserEmail)
                    ->select('role', 'teacher')
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    ->screenshot('filled-user-form')
                    ->press('Create User')
                    ->assertPathIs('/admin/users')
                    ->assertSee('User created successfully')
                    ->assertSee('New Teacher User')
                    ->assertSee($newUserEmail)
                    ->screenshot('after-user-created');

            // Verify the user was created in the database
            $this->assertDatabaseHas('users', [
                'email' => $newUserEmail,
                'role' => 'teacher',
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

        // Create a teacher to edit
        $teacher = User::factory()->create([
            'name' => 'Teacher To Edit',
            'email' => 'teacher_to_edit_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        $updatedName = 'Updated Teacher Name';

        $this->browse(function (Browser $browser) use ($admin, $teacher, $updatedName) {
            $browser->loginAs($admin)
                    ->visit('/admin/users')
                    ->screenshot('before-edit-user')
                    ->click('@edit-user-' . $teacher->id)
                    ->assertSee('Edit User')
                    ->assertInputValue('name', $teacher->name)
                    ->assertInputValue('email', $teacher->email)
                    ->assertSelected('role', 'teacher')
                    ->screenshot('edit-user-form')
                    ->type('name', $updatedName)
                    ->screenshot('updated-user-name')
                    ->press('Update User')
                    ->assertPathIs('/admin/users')
                    ->assertSee('User updated successfully')
                    ->assertSee($updatedName)
                    ->screenshot('after-user-updated');

            // Verify the user was updated in the database
            $this->assertDatabaseHas('users', [
                'id' => $teacher->id,
                'name' => $updatedName,
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

        // Create a student to delete
        $student = User::factory()->create([
            'name' => 'Student To Delete',
            'email' => 'student_to_delete_' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $student) {
            $browser->loginAs($admin)
                    ->visit('/admin/users')
                    ->screenshot('before-delete-user')
                    ->assertSee($student->name)
                    ->assertSee($student->email)
                    ->click('@delete-user-' . $student->id)
                    ->acceptDialog() // Accept the confirmation dialog
                    ->assertPathIs('/admin/users')
                    ->assertSee('User deleted successfully')
                    ->assertDontSee($student->email)
                    ->screenshot('after-user-deleted');

            // Verify the user was deleted from the database
            $this->assertDatabaseMissing('users', [
                'id' => $student->id,
            ]);
        });
    }
} 