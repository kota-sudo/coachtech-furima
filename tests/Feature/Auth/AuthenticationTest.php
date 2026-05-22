<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    public function test_email_is_required(): void
    {
        $this->from('/login')
            ->post('/login', [
                'email' => '',
                'password' => 'password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    public function test_password_is_required(): void
    {
        $this->from('/login')
            ->post('/login', [
                'email' => 'test@example.com',
                'password' => '',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    public function test_users_can_not_authenticate_with_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $this->from('/login')
            ->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
