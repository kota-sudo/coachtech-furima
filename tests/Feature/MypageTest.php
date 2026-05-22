<?php

namespace Tests\Feature;

use App\Models\Condition;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MypageTest extends TestCase
{
    use RefreshDatabase;

    private function createItem(User $seller, array $attributes = [], string $name = 'テスト商品'): Item
    {
        $condition = Condition::create(['name' => '良好']);

        $item = Item::create(array_merge([
            'user_id' => $seller->id,
            'condition_id' => $condition->id,
            'name' => $name,
            'brand_name' => null,
            'description' => '説明',
            'price' => 1000,
            'is_sold' => false,
        ], $attributes));

        ItemImage::create([
            'item_id' => $item->id,
            'image_path' => 'items/test.jpg',
            'sort_order' => 0,
        ]);

        return $item;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('mypage.index'))->assertRedirect(route('login'));
        $this->get(route('mypage.index', ['page' => 'buy']))->assertRedirect(route('login'));
    }

    public function test_mypage_displays_profile_name_and_image(): void
    {
        $user = User::factory()->create([
            'name' => 'マイページユーザー',
            'profile_image' => 'profile_images/test.jpg',
        ]);

        $this->actingAs($user)
            ->get(route('mypage.index'))
            ->assertOk()
            ->assertSee('マイページユーザー', false)
            ->assertSee('profile_images/test.jpg', false)
            ->assertSee(route('mypage.profile'), false)
            ->assertSee('プロフィールを編集', false);
    }

    public function test_mypage_displays_placeholder_when_profile_image_is_missing(): void
    {
        $user = User::factory()->create(['name' => '画像なしユーザー', 'profile_image' => null]);

        $this->actingAs($user)
            ->get(route('mypage.index'))
            ->assertOk()
            ->assertSee('No Image', false);
    }

    public function test_mypage_defaults_to_sell_tab(): void
    {
        $user = User::factory()->create();
        $this->createItem($user, [], '出品商品A');

        $this->actingAs($user)
            ->get(route('mypage.index'))
            ->assertOk()
            ->assertSee('出品した商品', false)
            ->assertSee('出品商品A', false)
            ->assertSee('border-indigo-500', false);
    }

    public function test_mypage_sell_tab_displays_listed_items(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $listed = $this->createItem($user, [], '自分の出品商品');
        $this->createItem($other, [], '他人の商品');

        $this->actingAs($user)
            ->get(route('mypage.index', ['page' => 'sell']))
            ->assertOk()
            ->assertSee('自分の出品商品', false)
            ->assertDontSee('他人の商品', false)
            ->assertSee(route('items.show', $listed), false);
    }

    public function test_mypage_buy_tab_displays_purchased_items(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $paymentMethod = PaymentMethod::create(['name' => 'カード支払い']);

        $purchased = $this->createItem($seller, [], '購入した商品');
        $notPurchased = $this->createItem($seller, [], '未購入商品');

        Purchase::create([
            'user_id' => $buyer->id,
            'item_id' => $purchased->id,
            'payment_method_id' => $paymentMethod->id,
            'postal_code' => '123-4567',
            'address' => '東京都',
        ]);

        $this->actingAs($buyer)
            ->get(route('mypage.index', ['page' => 'buy']))
            ->assertOk()
            ->assertSee('購入した商品', false)
            ->assertDontSee('未購入商品', false)
            ->assertSee(route('items.show', $purchased), false);
    }

    public function test_sold_badge_is_displayed_for_sold_items(): void
    {
        $user = User::factory()->create();
        $this->createItem($user, ['is_sold' => true], '売却済み出品');

        $this->actingAs($user)
            ->get(route('mypage.index', ['page' => 'sell']))
            ->assertOk()
            ->assertSee('Sold', false);
    }

    public function test_sold_badge_is_displayed_when_purchase_exists(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $paymentMethod = PaymentMethod::create(['name' => 'カード支払い']);

        $item = $this->createItem($seller, [], '購入済み表示商品');

        Purchase::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'payment_method_id' => $paymentMethod->id,
            'postal_code' => '123-4567',
            'address' => '東京都',
        ]);

        $this->actingAs($buyer)
            ->get(route('mypage.index', ['page' => 'buy']))
            ->assertOk()
            ->assertSee('Sold', false);
    }

    public function test_invalid_page_parameter_defaults_to_sell_tab(): void
    {
        $user = User::factory()->create();
        $this->createItem($user, [], 'デフォルト出品');

        $this->actingAs($user)
            ->get(route('mypage.index', ['page' => 'invalid']))
            ->assertOk()
            ->assertSee('デフォルト出品', false)
            ->assertDontSee('購入した商品はありません。', false);
    }
}
