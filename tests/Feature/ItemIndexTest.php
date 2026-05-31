<?php

namespace Tests\Feature;

use App\Models\Condition;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\Like;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemIndexTest extends TestCase
{
    use RefreshDatabase;

    private function createItem(User $seller, array $attributes = [], ?string $imagePath = 'items/test.jpg'): Item
    {
        $condition = Condition::first() ?? Condition::create(['name' => '良好']);

        $item = Item::create(array_merge([
            'user_id' => $seller->id,
            'condition_id' => $condition->id,
            'name' => 'テスト商品',
            'brand_name' => null,
            'description' => '説明',
            'price' => 1000,
            'is_sold' => false,
        ], $attributes));

        if ($imagePath !== null) {
            ItemImage::create([
                'item_id' => $item->id,
                'image_path' => $imagePath,
                'sort_order' => 0,
            ]);
        }

        return $item;
    }

    public function test_guest_can_view_item_index(): void
    {
        $seller = User::factory()->create();
        $this->createItem($seller, ['name' => '腕時計']);

        $this->get('/')
            ->assertOk()
            ->assertSee('腕時計', false);
    }

    public function test_authenticated_user_does_not_see_own_items(): void
    {
        $seller = User::factory()->create();
        $other = User::factory()->create();

        $this->createItem($seller, ['name' => '自分の商品']);
        $this->createItem($other, ['name' => '他人の商品']);

        $this->actingAs($seller)
            ->get('/')
            ->assertOk()
            ->assertDontSee('自分の商品', false)
            ->assertSee('他人の商品', false);
    }

    public function test_sold_items_display_sold_badge(): void
    {
        $seller = User::factory()->create();

        $this->createItem($seller, ['name' => '売却済み商品', 'is_sold' => true]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Sold', false);
    }

    public function test_purchased_items_display_sold_badge(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $item = $this->createItem($seller, ['name' => '購入済み商品', 'is_sold' => false]);

        Purchase::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'payment_method' => Purchase::PAYMENT_CARD,
            'postal_code' => '123-4567',
            'address' => '東京都',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Sold', false);
    }

    public function test_items_can_be_searched_by_keyword(): void
    {
        $seller = User::factory()->create();

        $this->createItem($seller, ['name' => '腕時計']);
        $this->createItem($seller, ['name' => '革靴']);

        $this->get('/?keyword=時計')
            ->assertOk()
            ->assertSee('腕時計', false)
            ->assertDontSee('革靴', false);
    }

    public function test_primary_image_uses_lowest_sort_order(): void
    {
        $seller = User::factory()->create();
        $condition = Condition::first() ?? Condition::create(['name' => '良好']);

        $item = Item::create([
            'user_id' => $seller->id,
            'condition_id' => $condition->id,
            'name' => '画像順序テスト',
            'description' => '説明',
            'price' => 1000,
            'is_sold' => false,
        ]);

        ItemImage::create([
            'item_id' => $item->id,
            'image_path' => 'items/second.jpg',
            'sort_order' => 1,
        ]);
        ItemImage::create([
            'item_id' => $item->id,
            'image_path' => 'items/first.jpg',
            'sort_order' => 0,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('items/first.jpg', false);
    }

    public function test_guest_mylist_shows_no_items(): void
    {
        $seller = User::factory()->create();
        $this->createItem($seller, ['name' => 'いいね対象商品']);

        $this->get('/?tab=mylist')
            ->assertOk()
            ->assertSee('マイリスト', false)
            ->assertDontSee('いいね対象商品', false);
    }

    public function test_authenticated_user_mylist_shows_only_liked_items(): void
    {
        $seller = User::factory()->create();
        $user = User::factory()->create();

        $likedItem = $this->createItem($seller, ['name' => 'いいね商品']);
        $otherItem = $this->createItem($seller, ['name' => '未いいね商品']);

        Like::create([
            'user_id' => $user->id,
            'item_id' => $likedItem->id,
        ]);

        $this->actingAs($user)
            ->get('/?tab=mylist')
            ->assertOk()
            ->assertSee('いいね商品', false)
            ->assertDontSee('未いいね商品', false);
    }

    public function test_mylist_can_be_searched_by_keyword(): void
    {
        $seller = User::factory()->create();
        $user = User::factory()->create();

        $watch = $this->createItem($seller, ['name' => '腕時計']);
        $shoes = $this->createItem($seller, ['name' => '革靴']);

        Like::create(['user_id' => $user->id, 'item_id' => $watch->id]);
        Like::create(['user_id' => $user->id, 'item_id' => $shoes->id]);

        $this->actingAs($user)
            ->get('/?tab=mylist&keyword=時計')
            ->assertOk()
            ->assertSee('腕時計', false)
            ->assertDontSee('革靴', false);
    }

    public function test_mylist_displays_sold_badge_for_liked_sold_item(): void
    {
        $seller = User::factory()->create();
        $user = User::factory()->create();

        $item = $this->createItem($seller, ['name' => '売却済みいいね商品', 'is_sold' => true]);

        Like::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->actingAs($user)
            ->get('/?tab=mylist')
            ->assertOk()
            ->assertSee('Sold', false);
    }

    public function test_mylist_tab_is_highlighted(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/?tab=mylist')
            ->assertOk()
            ->assertSee('border-red-500 text-red-500', false);
    }
}
