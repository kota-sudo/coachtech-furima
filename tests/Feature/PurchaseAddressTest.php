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

class PurchaseAddressTest extends TestCase
{
    use RefreshDatabase;

    private function createItem(User $seller, array $attributes = []): Item
    {
        $condition = Condition::create(['name' => '良好']);

        $item = Item::create(array_merge([
            'user_id' => $seller->id,
            'condition_id' => $condition->id,
            'name' => '住所変更テスト商品',
            'brand_name' => null,
            'description' => '説明',
            'price' => 5000,
            'is_sold' => false,
        ], $attributes));

        ItemImage::create([
            'item_id' => $item->id,
            'image_path' => 'items/test.jpg',
            'sort_order' => 0,
        ]);

        return $item;
    }

    private function seedPaymentMethod(): PaymentMethod
    {
        return PaymentMethod::create(['name' => 'カード支払い']);
    }

    public function test_guest_is_redirected_to_login_when_accessing_address_page(): void
    {
        $item = $this->createItem(User::factory()->create());

        $this->get(route('purchases.address', $item))
            ->assertRedirect(route('login'));
    }

    public function test_user_cannot_access_address_page_for_own_item(): void
    {
        $seller = User::factory()->create();
        $item = $this->createItem($seller);

        $this->actingAs($seller)
            ->get(route('purchases.address', $item))
            ->assertForbidden();
    }

    public function test_user_cannot_access_address_page_for_sold_item(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createItem($seller, ['is_sold' => true]);

        $this->actingAs($buyer)
            ->get(route('purchases.address', $item))
            ->assertForbidden();
    }

    public function test_purchase_page_links_to_address_change_page(): void
    {
        $this->seedPaymentMethod();
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createItem($seller);

        $this->actingAs($buyer)
            ->get(route('purchases.create', $item))
            ->assertOk()
            ->assertSee(route('purchases.address', $item), false)
            ->assertSee('送付先を変更する', false);
    }

    public function test_address_page_uses_user_defaults_when_session_is_empty(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create([
            'postal_code' => '100-0001',
            'address' => '東京都千代田区',
            'building' => '皇居',
        ]);
        $item = $this->createItem($seller);

        $this->actingAs($buyer)
            ->get(route('purchases.address', $item))
            ->assertOk()
            ->assertSee('送付先住所の変更', false)
            ->assertSee('value="100-0001"', false)
            ->assertSee('東京都千代田区', false)
            ->assertSee('皇居', false);
    }

    public function test_user_can_update_address_and_return_to_purchase_page(): void
    {
        $this->seedPaymentMethod();
        $seller = User::factory()->create();
        $buyer = User::factory()->create([
            'postal_code' => '100-0001',
            'address' => '東京都千代田区',
            'building' => null,
        ]);
        $item = $this->createItem($seller);

        $this->actingAs($buyer)
            ->put(route('purchases.address.update', $item), [
                'postal_code' => '150-0001',
                'address' => '東京都渋谷区',
                'building' => '渋谷ビル',
            ])
            ->assertRedirect(route('purchases.create', $item));

        $this->actingAs($buyer)
            ->get(route('purchases.create', $item))
            ->assertOk()
            ->assertSee('150-0001', false)
            ->assertSee('東京都渋谷区', false)
            ->assertSee('渋谷ビル', false)
            ->assertDontSee('100-0001', false);
    }

    public function test_purchase_saves_session_address_without_updating_user(): void
    {
        $paymentMethod = $this->seedPaymentMethod();
        $seller = User::factory()->create();
        $buyer = User::factory()->create([
            'postal_code' => '100-0001',
            'address' => '東京都千代田区',
            'building' => '皇居',
        ]);
        $item = $this->createItem($seller);

        $this->actingAs($buyer)
            ->put(route('purchases.address.update', $item), [
                'postal_code' => '160-0022',
                'address' => '東京都新宿区',
                'building' => '新宿タワー',
            ]);

        $this->actingAs($buyer)
            ->get(route('purchases.create', $item));

        $this->actingAs($buyer)
            ->post(route('purchases.store', $item), [
                'payment_method_id' => $paymentMethod->id,
                'postal_code' => '160-0022',
                'address' => '東京都新宿区',
                'building' => '新宿タワー',
            ])
            ->assertRedirect(route('items.index'));

        $this->assertDatabaseHas('purchases', [
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'postal_code' => '160-0022',
            'address' => '東京都新宿区',
            'building' => '新宿タワー',
        ]);

        $buyer->refresh();
        $this->assertSame('100-0001', $buyer->postal_code);
        $this->assertSame('東京都千代田区', $buyer->address);
        $this->assertSame('皇居', $buyer->building);
    }

    public function test_postal_code_is_required_on_address_update(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createItem($seller);

        $this->actingAs($buyer)
            ->from(route('purchases.address', $item))
            ->put(route('purchases.address.update', $item), [
                'postal_code' => '',
                'address' => '東京都',
            ])
            ->assertRedirect(route('purchases.address', $item))
            ->assertSessionHasErrors([
                'postal_code' => '郵便番号を入力してください',
            ]);
    }

    public function test_postal_code_must_match_hyphenated_format(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createItem($seller);

        $this->actingAs($buyer)
            ->from(route('purchases.address', $item))
            ->put(route('purchases.address.update', $item), [
                'postal_code' => '1234567',
                'address' => '東京都',
            ])
            ->assertRedirect(route('purchases.address', $item))
            ->assertSessionHasErrors([
                'postal_code' => '郵便番号はハイフンありの8文字で入力してください',
            ]);
    }

    public function test_building_is_optional_on_address_update(): void
    {
        $this->seedPaymentMethod();
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = $this->createItem($seller);

        $this->actingAs($buyer)
            ->put(route('purchases.address.update', $item), [
                'postal_code' => '123-4567',
                'address' => '東京都港区',
            ])
            ->assertRedirect(route('purchases.create', $item));

        $sessionKey = 'purchase_address.'.$item->id;
        $this->assertSame('123-4567', session($sessionKey)['postal_code']);
        $this->assertNull(session($sessionKey)['building']);
    }
}
