<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Condition;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    private function createItem(User $seller): Item
    {
        $condition = Condition::create(['name' => '良好']);

        return Item::create([
            'user_id' => $seller->id,
            'condition_id' => $condition->id,
            'name' => 'テスト商品',
            'brand_name' => null,
            'description' => '説明',
            'price' => 1000,
            'is_sold' => false,
        ]);
    }

    public function test_guest_is_redirected_to_login_when_commenting(): void
    {
        $item = $this->createItem(User::factory()->create());

        $this->post(route('items.comment', $item), [
            'comment' => 'テストコメント',
        ])->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_post_comment(): void
    {
        $seller = User::factory()->create();
        $user = User::factory()->create(['name' => 'コメント投稿者']);
        $item = $this->createItem($seller);

        $this->actingAs($user)
            ->post(route('items.comment', $item), [
                'comment' => 'とても良い商品です。',
            ])
            ->assertRedirect(route('items.show', $item));

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'comment' => 'とても良い商品です。',
        ]);

        $this->actingAs($user)
            ->get(route('items.show', $item))
            ->assertOk()
            ->assertSee('コメント投稿者', false)
            ->assertSee('とても良い商品です。', false)
            ->assertSee('コメント（1）', false);
    }

    public function test_comment_is_required(): void
    {
        $user = User::factory()->create();
        $item = $this->createItem(User::factory()->create());

        $this->actingAs($user)
            ->from(route('items.show', $item))
            ->post(route('items.comment', $item), [
                'comment' => '',
            ])
            ->assertRedirect(route('items.show', $item))
            ->assertSessionHasErrors([
                'comment' => 'コメントを入力してください',
            ]);
    }

    public function test_comment_must_not_exceed_255_characters(): void
    {
        $user = User::factory()->create();
        $item = $this->createItem(User::factory()->create());

        $this->actingAs($user)
            ->from(route('items.show', $item))
            ->post(route('items.comment', $item), [
                'comment' => str_repeat('あ', 256),
            ])
            ->assertRedirect(route('items.show', $item))
            ->assertSessionHasErrors([
                'comment' => 'コメントは255文字以内で入力してください',
            ]);
    }

    public function test_comment_count_increases_after_posting(): void
    {
        $user = User::factory()->create();
        $item = $this->createItem(User::factory()->create());

        $this->actingAs($user)
            ->post(route('items.comment', $item), [
                'comment' => '新しいコメント',
            ]);

        $this->assertSame(1, Comment::where('item_id', $item->id)->count());
    }
}
