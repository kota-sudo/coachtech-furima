<?php

namespace Tests\Feature;

use App\Models\Condition;
use App\Models\Item;
use App\Models\Like;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
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

    public function test_guest_is_redirected_to_login_when_liking(): void
    {
        $item = $this->createItem(User::factory()->create());

        $this->post(route('items.like', $item))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_add_like(): void
    {
        $seller = User::factory()->create();
        $user = User::factory()->create();
        $item = $this->createItem($seller);

        $this->actingAs($user)
            ->post(route('items.like', $item))
            ->assertRedirect(route('items.show', $item));

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->assertSame(1, Like::where('item_id', $item->id)->count());

        $this->actingAs($user)
            ->get(route('items.show', $item))
            ->assertOk()
            ->assertSee('text-red-500 fill-red-500', false);
    }

    public function test_authenticated_user_can_remove_like(): void
    {
        $seller = User::factory()->create();
        $user = User::factory()->create();
        $item = $this->createItem($seller);

        Like::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->actingAs($user)
            ->post(route('items.like', $item))
            ->assertRedirect(route('items.show', $item));

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->actingAs($user)
            ->get(route('items.show', $item))
            ->assertOk()
            ->assertSee('text-gray-400 fill-none', false);
    }

    public function test_like_count_decreases_when_unliked(): void
    {
        $seller = User::factory()->create();
        $user = User::factory()->create();
        $item = $this->createItem($seller);

        Like::create(['user_id' => $user->id, 'item_id' => $item->id]);

        $this->actingAs($user)
            ->post(route('items.like', $item));

        $item->refresh();
        $this->assertSame(0, $item->likes()->count());
    }

    public function test_liked_item_appears_in_mylist(): void
    {
        $seller = User::factory()->create();
        $user = User::factory()->create();
        $item = $this->createItem($seller);

        $this->actingAs($user)
            ->post(route('items.like', $item));

        $this->actingAs($user)
            ->get('/?tab=mylist')
            ->assertOk()
            ->assertSee('テスト商品', false);
    }

    public function test_unliked_item_is_removed_from_mylist(): void
    {
        $seller = User::factory()->create();
        $user = User::factory()->create();
        $item = $this->createItem($seller);

        Like::create(['user_id' => $user->id, 'item_id' => $item->id]);

        $this->actingAs($user)
            ->post(route('items.like', $item));

        $this->actingAs($user)
            ->get('/?tab=mylist')
            ->assertOk()
            ->assertDontSee('テスト商品', false);
    }
}
