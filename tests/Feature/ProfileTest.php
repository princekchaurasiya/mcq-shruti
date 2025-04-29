<?php

use App\Models\User;
use Livewire\Volt\Volt;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/profile');

    $response
        ->assertOk()
        ->assertViewIs('profile.edit')
        ->assertSee('Profile Information')
        ->assertSee('Update Password')
        ->assertSee('Delete Account');
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->patch('/profile', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->patch('/profile', [
        'name' => 'Test User',
        'email' => $user->email,
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->delete('/profile', [
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->delete('/profile', [
        'password' => 'wrong-password',
    ]);

    $response
        ->assertSessionHasErrors('password');

    $this->assertNotNull($user->fresh());
});
