<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('valid login redirects user to products', function (): void {
    $user = User::factory()->create([
        'email'    => 'user@test.com',
        'password' => Hash::make('password'),
        'role'     => 'user',
    ]);

    $response = $this->post('/login', [
        'email'    => 'user@test.com',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('products.index'));
    $this->assertAuthenticatedAs($user);
});

test('valid login redirects admin to admin orders', function (): void {
    $admin = User::factory()->admin()->create([
        'email'    => 'admin@test.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->post('/login', [
        'email'    => 'admin@test.com',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('admin.orders.index'));
    $this->assertAuthenticatedAs($admin);
});

test('invalid credentials return validation error', function (): void {
    User::factory()->create(['email' => 'user@test.com', 'password' => Hash::make('password')]);

    $response = $this->post('/login', [
        'email'    => 'user@test.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertSessionHasErrors('email');
});

test('login throttles after 6 attempts', function (): void {
    User::factory()->create(['email' => 'user@test.com', 'password' => Hash::make('password')]);

    for ($i = 0; $i < 6; $i++) {
        $this->post('/login', ['email' => 'user@test.com', 'password' => 'wrong']);
    }

    $response = $this->post('/login', ['email' => 'user@test.com', 'password' => 'wrong']);
    // After 6 failed attempts Laravel returns HTTP 429 Too Many Requests
    $response->assertStatus(429);
});
