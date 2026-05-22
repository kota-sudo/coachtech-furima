<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('mypage.profile', absolute: false));
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_name_is_required(): void
    {
        $this->from('/register')
            ->post('/register', [
                'name' => '',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    public function test_email_is_required(): void
    {
        $this->from('/register')
            ->post('/register', [
                'name' => 'Test User',
                'email' => '',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    public function test_password_is_required(): void
    {
        $this->from('/register')
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    public function test_password_must_be_at_least_8_characters(): void
    {
        $this->from('/register')
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => '1234567',
                'password_confirmation' => '1234567',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    public function test_password_confirmation_must_match(): void
    {
        $this->from('/register')
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'different',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
    }
}
