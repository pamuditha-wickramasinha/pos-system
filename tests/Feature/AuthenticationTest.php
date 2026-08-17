<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_bcrypt_password(): void
    {
        $user = User::create([
            'username' => 'jane',
            'password' => 'secret123',
            'status' => true,
        ]);

        $response = $this->post('/login/verify', [
            'username' => 'jane',
            'pass' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_legacy_md5_password_and_gets_rehashed(): void
    {
        $user = User::create([
            'username' => 'legacyuser',
            'password' => 'placeholder',
            'legacy_password' => md5('oldpass123'),
            'status' => true,
        ]);

        $response = $this->post('/login/verify', [
            'username' => 'legacyuser',
            'pass' => 'oldpass123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertNull($user->legacy_password);
        $this->assertTrue(Hash::check('oldpass123', $user->password));
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::create([
            'username' => 'jane',
            'password' => 'secret123',
            'status' => true,
        ]);

        $response = $this->post('/login/verify', [
            'username' => 'jane',
            'pass' => 'wrongpassword',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_disabled_user_cannot_login(): void
    {
        User::create([
            'username' => 'inactive',
            'password' => 'secret123',
            'status' => false,
        ]);

        $response = $this->post('/login/verify', [
            'username' => 'inactive',
            'pass' => 'secret123',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
