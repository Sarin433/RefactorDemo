<?php

use App\Models\User;

test('registration creates user with hashed password', function (): void {
    $response = $this->post('/register', [
        'email'                 => 'newuser@test.com',
        'first_name'            => 'Test',
        'last_name'             => 'User',
        'phone'                 => '0812345678',
        'password'              => 'P@ssword123!',
        'password_confirmation' => 'P@ssword123!',
    ]);

    $response->assertRedirect(route('products.index'));

    $user = User::where('email', 'newuser@test.com')->first();
    expect($user)->not->toBeNull();
    // OWASP A02: password must be bcrypt hash, not plain text
    expect($user->password)->toStartWith('$2y$');
});

test('registration with duplicate email fails', function (): void {
    User::factory()->create(['email' => 'dup@test.com']);

    $response = $this->post('/register', [
        'email'                 => 'dup@test.com',
        'first_name'            => 'A',
        'last_name'             => 'B',
        'phone'                 => '0812345678',
        'password'              => 'P@ssword123!',
        'password_confirmation' => 'P@ssword123!',
    ]);

    $response->assertSessionHasErrors('email');
});

test('registration with mismatched password fails', function (): void {
    $response = $this->post('/register', [
        'email'                 => 'newuser@test.com',
        'first_name'            => 'Test',
        'last_name'             => 'User',
        'phone'                 => '0812345678',
        'password'              => 'P@ssword123!',
        'password_confirmation' => 'DifferentPassword!',
    ]);

    $response->assertSessionHasErrors('password');
});
