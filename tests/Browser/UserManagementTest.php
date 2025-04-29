<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UserManagementTest extends DuskTestCase
{
    /**
     * Test admin can view users list
     */
    public function test_admin_can_view_users(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'email' => 'admin_users@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/dashboard')
                    ->clickLink('Users')
                    ->screenshot('users-list')
                    ->assertSee('Users List');
        });
    }

    /**
     * Test admin can create a new user
     */
    public function test_admin_can_create_user(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'email' => 'admin_create@example.com',
            'password' => bcrypt('password'),
        ]);

        $newUserEmail = 'new_user_' . rand(1000, 9999) . '@example.com';
        $newUserName = 'Test User ' . rand(100, 999);

        $this->browse(function (Browser $browser) use ($admin, $newUserEmail, $newUserName) {
            $browser->loginAs($admin)
                    ->visit('/dashboard')
                    ->clickLink('Users')
                    ->clickLink('Add New User')
                    ->screenshot('create-user-form')
                    ->type('name', $newUserName)
                    ->type('email', $newUserEmail)
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    ->select('role', 'student') // Adjust role selection based on your app
                    ->press('Create User')
                    ->screenshot('user-created')
                    ->assertSee($newUserEmail);
        });
    }

    /**
     * Test admin can edit a user
     */
    public function test_admin_can_edit_user(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'email' => 'admin_edit@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create a user to edit
        $user = User::factory()->create([
            'name' => 'User To Edit',
            'email' => 'edit_me@example.com',
            'password' => bcrypt('password'),
        ]);

        $updatedName = 'Updated Name ' . rand(100, 999);

        $this->browse(function (Browser $browser) use ($admin, $user, $updatedName) {
            $browser->loginAs($admin)
                    ->visit('/dashboard')
                    ->clickLink('Users')
                    ->screenshot('users-before-edit')
                    // Find and click edit for the user
                    ->clickLink('Edit', $user->id) // This may need adjustment based on your UI
                    ->screenshot('edit-user-form')
                    ->type('name', $updatedName)
                    ->press('Update User')
                    ->screenshot('user-updated')
                    ->assertSee($updatedName);
        });
    }
} 