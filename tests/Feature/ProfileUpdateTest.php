<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_requires_authentication(): void
    {
        $this->get('/mypage/profile')->assertRedirect(route('login'));
    }

    public function test_user_can_update_profile_with_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'name' => '初期名',
            'postal_code' => '100-0001',
            'address' => '東京都',
            'building' => 'ビル',
        ]);

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        $file = UploadedFile::fake()->createWithContent('avatar.png', $png);

        $response = $this->actingAs($user)->put('/mypage/profile', [
            'name' => '更新後',
            'postal_code' => '123-4567',
            'address' => '東京都港区',
            'building' => 'ACビル',
            'profile_image' => $file,
        ]);

        $response->assertRedirect(route('mypage.profile'));
        $response->assertSessionHas('status', 'profile-updated');

        $user->refresh();
        $this->assertSame('更新後', $user->name);
        $this->assertSame('123-4567', $user->postal_code);
        $this->assertSame('東京都港区', $user->address);
        $this->assertNotNull($user->profile_image);
        Storage::disk('public')->assertExists($user->profile_image);

        $this->actingAs($user)
            ->get('/mypage/profile')
            ->assertOk()
            ->assertSee('更新後', false);
    }

    public function test_profile_update_validates_postal_code_format(): void
    {
        $user = User::factory()->create([
            'postal_code' => '100-0001',
            'address' => '東京都',
        ]);

        $this->actingAs($user)
            ->put('/mypage/profile', [
                'name' => 'テストユーザー',
                'postal_code' => '1234567',
                'address' => '東京都港区',
            ])
            ->assertSessionHasErrors([
                'postal_code' => '郵便番号はハイフンありの8文字で入力してください',
            ]);
    }
}
